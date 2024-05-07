<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ArrayObject;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function array_filter;
use function array_intersect;
use function array_intersect_key;
use function array_map;
use function array_unique;
use function assert;
use function count;
use function in_array;
use function is_array;
use function is_string;
use function method_exists;
use function serialize;
use function sprintf;
use function ucfirst;
use function unserialize;

/**
 * Class SelectController.
 *
 * This class handles multiple actions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SelectHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use CallActionTrait;

    /**
     * SelectHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->getScopeDeterminator()->currentScopeIsBackend()) {
            return;
        }

        $action = $event->getAction();

        if ('select' !== $action->getName()) {
            return;
        }

        if (null !== ($response = $this->process($action, $event->getEnvironment()))) {
            $event->setResponse($response);

            // Stop the event here.
            // Don´t allow any listener for manipulation here.
            // Use the sub events there are called.
            $event->stopPropagation();
        }
    }

    /**
     * Handle the action.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|null
     */
    private function process(Action $action, EnvironmentInterface $environment)
    {
        $actionMethod = sprintf(
            'handle%sAllAction',
            ucfirst($this->getSubmitAction($environment, $this->regardSelectMode($environment)))
        );

        if (null !== ($response = $this->{$actionMethod}($environment, $action))) {
            return $response;
        }

        return null;
    }

    /**
     * Get the submit action name.
     *
     * @param EnvironmentInterface $environment      The environment.
     * @param boolean              $regardSelectMode Determine regard the select mode parameter.
     *
     * @return string
     */
    private function getSubmitAction(EnvironmentInterface $environment, $regardSelectMode = false)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if (
            !$regardSelectMode
            && $inputProvider->hasParameter('select')
            && !$inputProvider->hasValue('properties')
        ) {
            return 'select' . ucfirst($inputProvider->getParameter('select'));
        }

        if (null !== ($action = $this->determineAction($environment))) {
            return $action;
        }

        if ($regardSelectMode) {
            return $inputProvider->getParameter('mode') ?: '';
        }

        return $inputProvider->getParameter('select') ?
            'select' . ucfirst($inputProvider->getParameter('select')) : '';
    }

    /**
     * Determine the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|null
     */
    private function determineAction(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        foreach (['delete', 'cut', 'copy', 'override', 'edit'] as $action) {
            if (
                $inputProvider->hasValue($action)
                || $inputProvider->hasValue($action . '_save')
                || $inputProvider->hasValue($action . '_saveNback')
            ) {
                $inputProvider->setParameter('mode', $action);

                return $action;
            }
        }

        return null;
    }

    /**
     * Determine regard select mode.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function regardSelectMode(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $regardSelectMode = false;
        array_map(
            function ($value) use ($inputProvider, &$regardSelectMode) {
                if (!$inputProvider->hasValue($value)) {
                    return false;
                }

                $regardSelectMode = true;
                return true;
            },
            ['edit_save', 'edit_saveNback', 'override_save', 'override_saveNback', 'delete', 'copy', 'cut']
        );

        if (
            ('auto' === $inputProvider->getValue('SUBMIT_TYPE'))
            && ('edit' === $inputProvider->getParameter('select'))
        ) {
            return true;
        }

        return $regardSelectMode;
    }

    /**
     * Get The model ids from the environment.
     *
     * @param EnvironmentInterface $environment  The environment.
     * @param Action               $action       The dcg action.
     * @param string               $submitAction The submit action name.
     *
     * @return list<ModelIdInterface>
     */
    private function getModelIds(EnvironmentInterface $environment, Action $action, string $submitAction): array
    {
        $valueKey = in_array($submitAction, ['edit', 'override']) ? 'properties' : 'models';

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $modelIds = array_values((array) $inputProvider->getValue($valueKey));

        if (empty($modelIds)) {
            return [];
        }
        $modelIds = array_map(
            static fn (string $modelId): ModelIdInterface => ModelId::fromSerialized($modelId),
            $modelIds
        );

        $event = new PrepareMultipleModelsActionEvent($environment, $action, $modelIds, $submitAction);
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        $dispatcher->dispatch($event, $event::NAME);

        return $event->getModelIds();
    }

    /**
     * Handel select model all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleSelectModelsAllAction(EnvironmentInterface $environment, Action $action)
    {
        $this->clearClipboard($environment);
        $this->handleGlobalCommands($environment);

        return $this->callAction($environment, 'selectModelAll');
    }

    /**
     * Handle the select property all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleSelectPropertiesAllAction(EnvironmentInterface $environment, Action $action)
    {
        $this->clearClipboard($environment);
        $this->handleGlobalCommands($environment);
        $this->handleSessionOverrideEditAll(
            $this->getModelIds($environment, $action, $this->getSubmitAction($environment)),
            'models',
            $environment
        );

        $collection = $this->getSelectCollection($environment);
        $this->setIntersectProperties($collection, $environment);
        $this->setIntersectValues($collection, $environment);

        return $this->callAction($environment, 'selectPropertyAll');
    }

    /**
     * Handle the delete all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleDeleteAllAction(EnvironmentInterface $environment, Action $action)
    {
        $this->clearClipboard($environment);
        $this->handleGlobalCommands($environment);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        foreach ($this->getModelIds($environment, $action, $this->getSubmitAction($environment)) as $modelId) {
            $inputProvider->setParameter('id', $modelId->getSerialized());

            $this->callAction($environment, 'delete');

            $inputProvider->unsetParameter('id');
        }

        ViewHelpers::redirectHome($environment);

        return null;
    }

    /**
     * Handle the cut all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleCutAllAction(EnvironmentInterface $environment, Action $action)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        foreach ($this->getModelIds($environment, $action, $this->getSubmitAction($environment)) as $modelId) {
            $inputProvider->setParameter('source', $modelId->getSerialized());

            $this->callAction($environment, 'cut');

            $inputProvider->unsetParameter('source');
        }

        ViewHelpers::redirectHome($environment);

        return null;
    }

    /**
     * Handle the copy all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleCopyAllAction(EnvironmentInterface $environment, Action $action)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        foreach ($this->getModelIds($environment, $action, $this->getSubmitAction($environment)) as $modelId) {
            $inputProvider->setParameter('source', $modelId->getSerialized());

            $this->callAction($environment, 'copy');

            $inputProvider->unsetParameter('source');
        }

        ViewHelpers::redirectHome($environment);

        return null;
    }

    /**
     * Handle the edit all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleEditAllAction(EnvironmentInterface $environment, Action $action)
    {
        $this->clearClipboard($environment);
        $this->handleGlobalCommands($environment);
        $this->handleSessionOverrideEditAll(
            $this->getModelIds($environment, $action, $this->getSubmitAction($environment)),
            'properties',
            $environment
        );

        return  $this->callAction($environment, 'editAll', ['mode' => 'edit']);
    }

    /**
     * Handle the override all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param Action               $action      The action.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function handleOverrideAllAction(EnvironmentInterface $environment, Action $action)
    {
        $this->clearClipboard($environment);
        $this->handleGlobalCommands($environment);
        $this->handleSessionOverrideEditAll(
            $this->getModelIds($environment, $action, $this->getSubmitAction($environment)),
            'properties',
            $environment
        );

        return $this->callAction($environment, 'overrideAll', ['mode' => 'override']);
    }

    /**
     * Handle the global commands.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function handleGlobalCommands(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $backendView = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        $globalCommands = $backendView->getGlobalCommands();
        assert($globalCommands instanceof CommandCollectionInterface);

        $backButton = null;
        if ($globalCommands->hasCommandNamed('back_button')) {
            $backButton = $globalCommands->getCommandNamed('back_button');
        }

        if (!$backButton) {
            return;
        }

        $parametersBackButton = $backButton->getParameters();

        if (in_array($this->getSelectAction($environment), ['properties', 'edit'])) {
            $parametersBackButton->offsetSet('act', 'select');
            $parametersBackButton->offsetSet(
                'select',
                ('edit' === $this->getSelectAction($environment)) ? 'properties' : 'models'
            );
            $parametersBackButton->offsetSet('mode', $this->getSubmitAction($environment, true));
        }

        $closeCommand = new Command();
        $backendView->getGlobalCommands()->addCommand($closeCommand);

        $closeExtra = [
            'href'       => $this->getReferrerUrl($environment),
            'class'      => 'header_icon header_stop',
            'accessKey'  => 'x',
            'attributes' => 'onclick="Backend.getScrollOffset();"'
        ];

        $closeCommand
            ->setName('close_all_button')
            ->setLabel('closeAll.label')
            ->setDescription('closeAll.description')
            ->setParameters(new ArrayObject())
            ->setExtra(new ArrayObject($closeExtra))
            ->setDisabled(false);
    }

    /**
     * Get the select action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function getSelectAction(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return $inputProvider->getParameter('select');
    }

    /**
     * Determine the correct referrer URL.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return mixed
     */
    private function getReferrerUrl(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $parentDefinition = $environment->getParentDataDefinition();
        $event = new GetReferrerEvent(
            true,
            (null !== $parentDefinition)
                ? $parentDefinition->getName()
                : $definition->getName()
        );

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, ContaoEvents::SYSTEM_GET_REFERRER);

        return $event->getReferrerUrl();
    }

    /**
     * Clear the clipboard if has items.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function clearClipboard(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $filter = new Filter();

        $dataProvider = $basicDefinition->getDataProvider();
        assert(is_string($dataProvider));

        $filter->andModelIsFromProvider($dataProvider);
        if (null !== ($parentProvider = $basicDefinition->getParentDataProvider())) {
            $filter->andParentIsFromProvider($parentProvider);
        } else {
            $filter->andHasNoParent();
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $items = $clipboard->fetch($filter);
        if (count($items) < 1) {
            return;
        }

        foreach ($items as $item) {
            $clipboard->remove($item);
        }
    }

    /**
     * Get all select models in a collection.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return CollectionInterface
     */
    private function getSelectCollection(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $session        =
            $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        $modelIds = [];
        foreach (($session['models'] ?? []) as $modelId) {
            $modelIds[] = ModelId::fromSerialized($modelId)->getId();
        }
        if ([] === $modelIds) {
            return $dataProvider->getEmptyCollection();
        }

        $idProperty = method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        $collection = $dataProvider->fetchAll(
            $dataProvider->getEmptyConfig()->setFilter(
                [
                    [
                        'operation' => 'IN',
                        'property'  => $idProperty,
                        'values'    => $modelIds
                    ]
                ]
            )
        );
        assert($collection instanceof CollectionInterface);
        return $collection;
    }

    /**
     * Set the intersect properties to the session.
     *
     * @param CollectionInterface  $collection  The collection of models.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function setIntersectProperties(CollectionInterface $collection, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $session =
            $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        $session['intersectProperties'] = $this->collectIntersectModelProperties($collection, $environment);
        $sessionStorage->set($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true), $session);
    }

    /**
     * Set the intersect properties to the session.
     *
     * @param CollectionInterface  $collection  The collection of models.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function setIntersectValues(CollectionInterface $collection, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        if (!$session['intersectProperties'] || !count($session['intersectProperties'])) {
            return;
        }

        $session['intersectValues'] = $this->collectIntersectValues($collection, $environment);
        $sessionStorage->set($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true), $session);
    }

    /**
     * Collecting intersect properties from the collection of models.
     *
     * @param CollectionInterface  $collection  The collection of models.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    private function collectIntersectModelProperties(CollectionInterface $collection, EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $palettesDefinition = $definition->getPalettesDefinition();

        $properties = [];
        foreach ($collection->getIterator() as $model) {
            $palette = $palettesDefinition->findPalette($model);

            $modelProperties = $this->getVisibleAndEditAbleProperties($palette, $model);
            foreach ($modelProperties as $modelProperty) {
                if (empty($properties[$modelProperty])) {
                    $properties[$modelProperty] = 0;
                }

                ++$properties[$modelProperty];
            }
        }

        // We always have to keep the id in the array.
        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);
        $idProperty = method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        $properties[$idProperty] = $collection->count();

        return array_filter(
            $properties,
            function ($count) use ($collection) {
                return $count === $collection->count();
            }
        );
    }

    /**
     * Collect the intersect values from the model collection.
     *
     * @param CollectionInterface  $collection  The collection.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    private function collectIntersectValues(CollectionInterface $collection, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        $values = [];
        foreach ($collection->getIterator() as $model) {
            $modelValues = array_intersect_key($model->getPropertiesAsArray(), $session['intersectProperties']);
            foreach ($modelValues as $modelProperty => $modelValue) {
                $values[$modelProperty][] = $modelValue;
            }
        }

        $intersectValues = [];
        foreach ($values as $propertyName => $propertyValues) {
            if (!($value = $this->getUniqueValueFromArray($propertyValues))) {
                continue;
            }

            $intersectValues[$propertyName] = $value;
        }

        return $intersectValues;
    }

    /**
     * Get the palette properties their are visible and editable.
     *
     * @param PaletteInterface $palette The palette.
     * @param ModelInterface   $model   The model.
     *
     * @return array
     */
    private function getVisibleAndEditAbleProperties(PaletteInterface $palette, ModelInterface $model)
    {
        return array_intersect(
            array_map(
                function (PropertyInterface $property) {
                    return $property->getName();
                },
                $palette->getVisibleProperties($model)
            ),
            array_map(
                function (PropertyInterface $property) {
                    return $property->getName();
                },
                $palette->getEditableProperties($model)
            )
        );
    }

    /**
     * Get the unique value from a array. The value will return if the all values in the array the same.
     *
     * @param array $values The values.
     *
     * @return string|array|null
     */
    private function getUniqueValueFromArray(array $values)
    {
        $serializedValues = false;
        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $values[$key] = serialize($value);

            $serializedValues = true;
        }

        if (!$serializedValues) {
            return 1 === count(array_unique($values)) ? $values[0] : null;
        }

        return 1 === count(array_unique($values)) ? unserialize($values[0], ['allowed_classes' => true]) : null;
    }

    /**
     * Handle session data for override/edit all.
     *
     * @param list<ModelIdInterface> $collection  The collection.
     * @param 'models'|'properties'  $index       The session index for the collection.
     * @param EnvironmentInterface   $environment The environment.
     *
     * @return list<
     *     ModelIdInterface> The collection.
     */
    private function handleSessionOverrideEditAll(array $collection, string $index, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        /** @var array{
         *     models: list<string>,
         *     intersectProperties: array<string, int>,
         *     intersectValues: array<string, mixed>,
         *     properties?: list<string>,
         *     editProperties?: list<string>,
         * } $session */
        $session = ['models' => [], 'intersectProperties' => [], 'intersectValues' => []];
        $sessionKey = $dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true);
        if ($sessionStorage->has($sessionKey)) {
            $session = $sessionStorage->get($sessionKey);
        }

        // If collection not empty set to the session and return it.
        if (!empty($collection)) {
            $sessionCollection = array_map(
                static fn (ModelIdInterface $item): string => $item->getSerialized(),
                $collection
            );

            $session[$index] = $sessionCollection;

            $sessionStorage->set($sessionKey, $session);

            return $collection;
        }

        // If the collection not in the session return the collection.
        if (empty($session[$index])) {
            return $collection;
        }

        // Get the verify collection from the session and return it.
        return array_map(
            static fn (string $item): ModelIdInterface => ModelId::fromSerialized($item),
            array_values($session[$index])
        );
    }
}
