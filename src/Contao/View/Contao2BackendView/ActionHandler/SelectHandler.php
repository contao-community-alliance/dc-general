<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
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
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;

/**
 * Class SelectController.
 *
 * This class handles multiple actions.
 *
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
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $action = $event->getAction();

        if ('select' !== $action->getName()) {
            return;
        }

        if (false !== ($response = $this->process($action, $event->getEnvironment()))) {
            $event->setResponse($response);

            // Stop the event here.
            // Don´t allow any listener for manipulation here.
            // Use the sub events their are called.
            $event->stopPropagation();
        }
    }

    /**
     * Handle the action.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function process(Action $action, EnvironmentInterface $environment)
    {
        $actionMethod = \sprintf(
            'handle%sAllAction',
            \ucfirst($this->getSubmitAction($environment, $this->regardSelectMode($environment)))
        );

        if (false !== ($response = $this->{$actionMethod}($environment, $action))) {
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
        if (!$regardSelectMode
            && $environment->getInputProvider()->hasParameter('select')
            && !$environment->getInputProvider()->hasValue('properties')
        ) {
            return 'select' . \ucfirst($environment->getInputProvider()->getParameter('select'));
        }

        if (null !== ($action = $this->determineAction($environment))) {
            return $action;
        }

        if ($regardSelectMode) {
            return $environment->getInputProvider()->getParameter('mode') ?: null;
        }

        return $environment->getInputProvider()->getParameter('select') ?
            'select' . \ucfirst($environment->getInputProvider()->getParameter('select')) : null;
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
        foreach (['delete', 'cut', 'copy', 'override', 'edit'] as $action) {
            if ($environment->getInputProvider()->hasValue($action)
                || $environment->getInputProvider()->hasValue($action . '_save')
                || $environment->getInputProvider()->hasValue($action . '_saveNback')
            ) {
                $environment->getInputProvider()->setParameter('mode', $action);

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

        $regardSelectMode = false;
        \array_map(
            function ($value) use ($inputProvider, &$regardSelectMode) {
                if (!$inputProvider->hasValue($value)) {
                    return false;
                }

                $regardSelectMode = true;
                return true;
            },
            ['edit_save', 'edit_saveNback', 'override_save', 'override_saveNback', 'delete', 'copy', 'cut']
        );

        if (('auto' === $inputProvider->getValue('SUBMIT_TYPE'))
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
     * @return ModelId[]
     */
    private function getModelIds(EnvironmentInterface $environment, Action $action, $submitAction)
    {
        $valueKey = \in_array($submitAction, ['edit', 'override']) ? 'properties' : 'models';
        $modelIds = (array) $environment->getInputProvider()->getValue($valueKey);

        if (!empty($modelIds)) {
            $modelIds = \array_map(
                function ($modelId) {
                    return ModelId::fromSerialized($modelId);
                },
                $modelIds
            );

            $event = new PrepareMultipleModelsActionEvent($environment, $action, $modelIds, $submitAction);
            $environment->getEventDispatcher()->dispatch($event, $event::NAME);

            $modelIds = $event->getModelIds();
        }

        return $modelIds;
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

        if ($response = $this->callAction($environment, 'selectModelAll')) {
            return $response;
        }

        return null;
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

        if ($response = $this->callAction($environment, 'selectPropertyAll')) {
            return $response;
        }

        return null;
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
     * @return string
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
     * @return string
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
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        $backButton = null;
        if ($backendView->getGlobalCommands()->hasCommandNamed('back_button')) {
            $backButton = $backendView->getGlobalCommands()->getCommandNamed('back_button');
        }

        if (!$backButton) {
            return;
        }

        $parametersBackButton = $backButton->getParameters();

        if (\in_array($this->getSelectAction($environment), ['properties', 'edit'])) {
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
            ->setLabel('MSC.closeAll.0')
            ->setDescription('MSC.closeAll.1')
            ->setParameters(new \ArrayObject())
            ->setExtra(new \ArrayObject($closeExtra))
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
        return $environment->getInputProvider()->getParameter('select');
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
        $event = new GetReferrerEvent(
            true,
            (null !== $environment->getParentDataDefinition())
                ? $environment->getParentDataDefinition()->getName()
                : $environment->getDataDefinition()->getName()
        );

        $environment->getEventDispatcher()->dispatch($event, ContaoEvents::SYSTEM_GET_REFERRER);

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
        $basicDefinition = $environment->getDataDefinition()->getBasicDefinition();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());
        } else {
            $filter->andHasNoParent();
        }

        $clipboard = $environment->getClipboard();

        $items = $clipboard->fetch($filter);
        if (\count($items) < 1) {
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
        $sessionStorage = $environment->getSessionStorage();
        $dataDefinition = $environment->getDataDefinition();
        $dataProvider   = $environment->getDataProvider();
        $session        =
            $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        $modelIds = [];
        foreach ($session['models'] as $modelId) {
            $modelIds[] = ModelId::fromSerialized($modelId)->getId();
        }

        $idProperty = \method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        return $dataProvider->fetchAll(
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
        $sessionStorage = $environment->getSessionStorage();
        $dataDefinition = $environment->getDataDefinition();

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
        $sessionStorage = $environment->getSessionStorage();
        $dataDefinition = $environment->getDataDefinition();

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        if (!$session['intersectProperties'] || !\count($session['intersectProperties'])) {
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
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        $properties = [];
        foreach ($collection->getIterator() as $model) {
            $palette = $palettesDefinition->findPalette($model);

            $modelProperties = $this->getVisibleAndEditAbleProperties($palette, $model);
            foreach ($modelProperties as $modelProperty) {
                if (!$properties[$modelProperty]) {
                    $properties[$modelProperty] = 0;
                }

                ++$properties[$modelProperty];
            }
        }

        return \array_filter(
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
        $sessionStorage = $environment->getSessionStorage();
        $dataDefinition = $environment->getDataDefinition();

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));

        $values = [];
        foreach ($collection->getIterator() as $model) {
            $modelValues = \array_intersect_key($model->getPropertiesAsArray(), $session['intersectProperties']);
            foreach ($modelValues as $modelProperty => $modelValue) {
                if (1 === $collection->count()) {
                    $values[$modelProperty] = $modelValue;

                    continue;
                }

                $values[$modelProperty][] = $modelValue;
            }
        }

        if (1 === $collection->count()) {
            return $values;
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
        return \array_intersect(
            \array_map(
                function (PropertyInterface $property) {
                    return $property->getName();
                },
                $palette->getVisibleProperties($model)
            ),
            \array_map(
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
            if (!\is_array($value)) {
                continue;
            }

            $values[$key] = \serialize($value);

            $serializedValues = true;
        }

        if (!$serializedValues) {
            return 1 === \count(\array_unique($values)) ? $values[0] : null;
        }

        return 1 === \count(\array_unique($values)) ? \unserialize($values[0], ['allowed_classes' => true]) : null;
    }

    /**
     * Handle session data for override/edit all.
     *
     * @param array                $collection  The collection.
     * @param string               $index       The session index for the collection.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array The collection.
     */
    private function handleSessionOverrideEditAll(array $collection, $index, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        $sessionStorage = $environment->getSessionStorage();

        $session = [];
        if ($sessionStorage->has($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true))) {
            $session =
                $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true));
        }

        // If collection not empty set to the session and return it.
        if (!empty($collection)) {
            $sessionCollection = \array_map(
                function ($item) use ($index) {
                    if (!\in_array($index, ['models', 'properties'])) {
                        return $item;
                    }

                    if (!$item instanceof ModelId) {
                        $item = ModelId::fromSerialized($item);
                    }

                    return $item->getSerialized();
                },
                $collection
            );

            $session[$index] = $sessionCollection;

            $sessionStorage
                ->set($dataDefinition->getName() . '.' . $this->getSubmitAction($environment, true), $session);

            return $collection;
        }

        // If the collection not in the session return the collection.
        if (empty($session[$index])) {
            return $collection;
        }

        // Get the verify collection from the session and return it.
        $collection = \array_map(
            function ($item) use ($index) {
                if (!\in_array($index, ['models', 'properties'])) {
                    return $item;
                }

                return ModelId::fromSerialized($item);
            },
            $session[$index]
        );

        return $collection;
    }
}
