<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Message;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\BackCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractHandler;

/**
 * Class SelectController.
 *
 * This class handles multiple actions.
 */
class SelectHandler extends AbstractHandler
{
    /**
     * Handle the action.
     *
     * @return void
     */
    public function process()
    {
        if (!$this->getSelectAction()
            || $this->getEvent()->getAction()->getName() !== 'select'
        ) {
            return;
        }

        $submitAction = $this->getSubmitAction(true);

        $this->removeGlobalCommands();
        $this->handleSessionBySelectAction();

        if ('models' === $this->getSelectAction()) {
            $this->handleBySelectActionModels();

            return;
        }

        $this->handleNonEditAction();
        $this->clearClipboardBySubmitAction();

        if ('properties' === $this->getSelectAction()) {
            $this->sessionSetIntersectValues();
            $this->handleBySelectActionProperties();

            return;
        }

        $this->handleNonSelectByShowAllAction();
        $this->handleGlobalCommands();

        Message::reset();
        $this->callAction($submitAction . 'All', array('mode' => $submitAction));
    }

    /**
     * Get the submit action name.
     *
     * @param boolean $regardSelectMode Regard the select mode parameter.
     *
     * @return string
     */
    private function getSubmitAction($regardSelectMode = false)
    {
        $actions = array('delete', 'cut', 'copy', 'override', 'edit');

        foreach ($actions as $action) {
            if ($this->getEnvironment()->getInputProvider()->hasValue($action)
                || $this->getEnvironment()->getInputProvider()->hasValue($action . '_save')
                || $this->getEnvironment()->getInputProvider()->hasValue($action . '_saveNback')
            ) {
                $this->getEnvironment()->getInputProvider()->setParameter('mode', $action);

                return $action;
            }
        }

        if ($regardSelectMode) {
            return $this->getEnvironment()->getInputProvider()->getParameter('mode') ?: null;
        }

        return null;
    }

    /**
     * Get the select action.
     *
     * @return string
     */
    private function getSelectAction()
    {
        return $this->getEnvironment()->getInputProvider()->getParameter('select');
    }

    /**
     * Handle by select action models.
     *
     * @return void
     */
    private function handleBySelectActionModels()
    {
        if ('models' !== $this->getSelectAction()) {
            return;
        }

        $this->clearClipboard();
        $this->handleGlobalCommands();

        $arguments           = $this->getEvent()->getAction()->getArguments();
        $arguments['mode']   = $this->getSubmitAction(true);
        $arguments['select'] = $this->getSelectAction();

        $this->callAction('showAll', $arguments);
    }

    /**
     * Handle by select action properties.
     *
     * @return void
     */
    private function handleBySelectActionProperties()
    {
        if ('properties' !== $this->getSelectAction()) {
            return;
        }

        $this->handleGlobalCommands();

        $arguments           = $this->getEvent()->getAction()->getArguments();
        $arguments['mode']   = $this->getSubmitAction(true);
        $arguments['select'] = $this->getSelectAction();

        $this->callAction('showAll', $arguments);
    }

    /**
     * Handle the session by select action.
     *
     * @return void
     */
    private function handleSessionBySelectAction()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        switch ($this->getSelectAction()) {
            case 'properties':
                if ($inputProvider->hasValue('models')) {
                    $models = $this->getModelIds($this->getEvent()->getAction(), $this->getSubmitAction());

                    $this->handleSessionOverrideEditAll($models, 'models');
                }

                break;

            case 'edit':
                if ($inputProvider->hasValue('properties')) {
                    $this->handleSessionOverrideEditAll($inputProvider->getValue('properties'), 'properties');
                }

                break;

            default:
        }
    }

    /**
     * Handle session data for override/edit all.
     *
     * @param array  $collection The collection.
     *
     * @param string $index      The session index for the collection.
     *
     * @return array The collection.
     */
    private function handleSessionOverrideEditAll(array $collection, $index)
    {
        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        $sessionStorage = $this->getEnvironment()->getSessionStorage();

        $session = array();
        if ($sessionStorage->has($dataDefinition->getName() . '.' . $this->getSubmitAction(true))) {
            $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction(true));
        }

        // If collection not empty set to the session and return it.
        if (!empty($collection)) {
            $sessionCollection = array_map(
                function ($item) use ($index) {
                    if (!in_array($index, array('models', 'properties'))) {
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

            $sessionStorage->set($dataDefinition->getName() . '.' . $this->getSubmitAction(true), $session);

            return $collection;
        }

        // If the collection not in the session return the collection.
        if (empty($session[$index])) {
            return $collection;
        }

        // Get the verify collection from the session and return it.
        $collection = array_map(
            function ($item) use ($index) {
                if (!in_array($index, array('models', 'properties'))) {
                    return $item;
                }

                return ModelId::fromSerialized($item);
            },
            $session[$index]
        );

        return $collection;
    }

    /**
     * Set the intersection values to the session.
     * If all select models has the same value by their properties, then is set the value.
     *
     * @return void
     */
    private function sessionSetIntersectValues()
    {
        $inputProvider    = $this->getEnvironment()->getInputProvider();
        $sessionStorage   = $this->getEnvironment()->getSessionStorage();
        $dataDefinition   = $this->getEnvironment()->getDataDefinition();
        $modelRelation    = $dataDefinition->getModelRelationshipDefinition();
        $parentDefinition = $this->getEnvironment()->getParentDataDefinition();

        $model = $this->getEnvironment()->getDataProvider()->getEmptyModel();
        $model->setId(99999999);
        if ($parentDefinition && $inputProvider->hasParameter('pid')) {
            $childCondition =
                $modelRelation->getChildCondition($parentDefinition->getName(), $dataDefinition->getName());

            $parentModelId = ModelId::fromSerialized($inputProvider->getParameter('pid'));

            foreach ($childCondition->getFilterArray() as $filter) {
                if (!$dataDefinition->getPropertiesDefinition()->hasProperty($filter['local'])) {
                    continue;
                }

                if ($dataDefinition->getPropertiesDefinition()->hasProperty($filter['local'])) {
                    $model->setProperty($filter['local'], $parentModelId->getId());
                }

                break;
            }
        }

        $this->setIntersectionValues($model);

        $session                    =
            $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction(true));
        $session['intersectValues'] = $model->getPropertiesAsArray();
        $sessionStorage->set($dataDefinition->getName() . '.' . $this->getSubmitAction(true), $session);
    }

    /**
     * Set the intersection values to the model.
     *
     * @param ModelInterface $model The model.
     *
     * @return void
     */
    private function setIntersectionValues(ModelInterface $model)
    {
        $sessionStorage       = $this->getEnvironment()->getSessionStorage();
        $dataDefinition       = $this->getEnvironment()->getDataDefinition();
        $dataProvider         = $this->getEnvironment()->getDataProvider();
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $this->getSubmitAction(true));

        $modelIds = array();
        foreach ($session['models'] as $modelId) {
            $modelIds[] = ModelId::fromSerialized($modelId)->getId();
        }

        $idProperty = (method_exists($dataProvider, 'getIdProperty')) ? $dataProvider->getIdProperty() : 'id';
        $collection = $dataProvider->fetchAll(
            $dataProvider->getEmptyConfig()->setFilter(
                array(
                    array(
                        'operation' => 'IN',
                        'property'  => $idProperty,
                        'values'    => $modelIds
                    )
                )
            )
        );

        $count       = $collection->count();
        $valuesCount = array();
        $values      = array();

        $this->getIntersectValues($collection, $values, $valuesCount);

        if (0 === count($values)) {
            return;
        }
        foreach ($values as $propertyName => $propertyValue) {
            if (!isset($valuesCount[$propertyName])
                || ($count !== $valuesCount[$propertyName])) {
                continue;
            }

            $property = $propertiesDefinition->getProperty($propertyName);
            if (is_numeric($propertyValue) && (null !== $property->getWidgetType())) {
                $propertyValue = (int) $propertyValue;

            }
            $model->setProperty($propertyName, $propertyValue);
        }
    }

    /**
     * Get the intersect values.
     *
     * @param CollectionInterface $collection  The collection.
     *
     * @param array               $values      The values.
     *
     * @param array               $valuesCount The count of values.
     *
     * @return void
     */
    private function getIntersectValues(CollectionInterface $collection, array &$values, array &$valuesCount)
    {
        while ($collection->count() > 0) {
            $intersectModel = $collection->shift();

            foreach ($intersectModel->getPropertiesAsArray() as $modelProperty => $modelValue) {
                if (!isset($valuesCount[$modelProperty])) {
                    $values[$modelProperty] = $modelValue;
                }

                if (isset($values[$modelProperty])
                    && ($modelValue !== $values[$modelProperty])) {
                    unset($values[$modelProperty]);
                    unset($valuesCount[$modelProperty]);
                }

                if (!isset($valuesCount[$modelProperty])) {
                    $valuesCount[$modelProperty] = 0;
                }

                if (!isset($valuesCount[$modelProperty])) {
                    continue;
                }

                ++$valuesCount[$modelProperty];
            }
        }
    }

    /**
     * This handle non edit action.
     *
     * @return void
     */
    private function handleNonEditAction()
    {
        $submitAction = $this->getSubmitAction();
        if (!in_array($submitAction, array('delete', 'copy', 'cut'))) {
            return;
        }

        switch ($submitAction) {
            case 'copy':
            case 'cut':
                $parameter = 'source';
                break;

            default:
                $parameter = 'id';
        }

        $modelIds = $this->getModelIds($this->getEvent()->getAction(), $submitAction);

        foreach ($modelIds as $modelId) {
            $this->getEnvironment()->getInputProvider()->setParameter($parameter, $modelId->getSerialized());
            $this->callAction($submitAction);
        }

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * If non select models or properties by show all action redirect to home.
     *
     * @return void
     */
    private function handleNonSelectByShowAllAction()
    {
        $submitAction = $this->getSubmitAction(true);
        if (in_array($submitAction, array('cut', 'delete', 'copy', 'override', 'edit'))) {
            return;
        }

        $inputProvider = $this->getEnvironment()->getInputProvider();
        $translator    = $this->getEnvironment()->getTranslator();

        $modelIds = $this->getModelIds($this->getEvent()->getAction(), $submitAction);

        if ((empty($modelIds)
             && $inputProvider->getValue($submitAction) !== $translator->translate('MSC.continue'))
            || ($inputProvider->getValue($submitAction) === $translator->translate('MSC.continue')
                && !$inputProvider->hasValue('properties'))
        ) {
            ViewHelpers::redirectHome($this->getEnvironment());
        }
    }

    /**
     * Remove the global commands by action select.
     * We need the back button only.
     *
     * @return void
     */
    private function removeGlobalCommands()
    {
        $dataDefinition = $this->getEvent()->getEnvironment()->getDataDefinition();
        $view           = $dataDefinition->getDefinition('view.contao2backend');

        foreach ($view->getGlobalCommands()->getCommands() as $globalCommand) {
            if (!($globalCommand instanceof BackCommand)) {
                $globalCommand->setDisabled();
            }
        }
    }

    /**
     * Handle the global commands.
     *
     * @return void
     */
    private function handleGlobalCommands()
    {
        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        $backButton = null;
        if ($backendView->getGlobalCommands()->hasCommandNamed('back_button')) {
            $backButton = $backendView->getGlobalCommands()->getCommandNamed('back_button');
        }

        if (!$backButton) {
            return;
        }

        $parametersBackButton = $backButton->getParameters();

        if (in_array($this->getSelectAction(), array('properties', 'edit'))) {
            $parametersBackButton->offsetSet('act', 'select');
            $parametersBackButton->offsetSet('select', ($this->getSelectAction() === 'edit') ? 'properties' : 'models');
            $parametersBackButton->offsetSet('mode', $this->getSubmitAction(true));
        }

        $closeCommand = new Command();
        $backendView->getGlobalCommands()->addCommand($closeCommand);

        $closeExtra = array(
            'href'       => $this->getReferrerUrl(),
            'class'      => 'header_logout',
            'icon'       => 'delete.gif',
            'accessKey'  => 'x',
            'attributes' => 'onclick="Backend.getScrollOffset();"'
        );

        $closeCommand
            ->setName('close_all_button')
            ->setLabel('MSC.closeAll.0')
            ->setDescription('MSC.closeAll.1')
            ->setParameters(new \ArrayObject())
            ->setExtra(new \ArrayObject($closeExtra))
            ->setDisabled(false);
    }

    /**
     * Determine the correct referrer URL.
     *
     * @return mixed
     */
    private function getReferrerUrl()
    {
        $event = new GetReferrerEvent(
            true,
            (null !== $this->getEnvironment()->getParentDataDefinition())
                ? $this->getEnvironment()->getParentDataDefinition()->getName()
                : $this->getEnvironment()->getDataDefinition()->getName()
        );

        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $event);

        return $event->getReferrerUrl();
    }

    /**
     * Get the model ids from the from input.
     *
     * @param Action $action       The dcg action.
     *
     * @param string $submitAction The submit action name.
     *
     * @return ModelId[]
     */
    private function getModelIds(Action $action, $submitAction)
    {
        $modelIds = (array) $this->getEnvironment()->getInputProvider()->getValue('models');

        if (!empty($modelIds)) {
            $modelIds = array_map(
                function ($modelId) {
                    return ModelId::fromSerialized($modelId);
                },
                $modelIds
            );

            $event = new PrepareMultipleModelsActionEvent($this->getEnvironment(), $action, $modelIds, $submitAction);
            $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);

            $modelIds = $event->getModelIds();
        }

        return $modelIds;
    }

    /**
     * Clear the clipboard by override/edit submit actions.
     *
     * @return void
     */
    private function clearClipboardBySubmitAction()
    {
        if (in_array($this->getSubmitAction(), array('edit', 'override'))) {
            return;
        }

        $this->clearClipboard();
    }

    /**
     * Clear the clipboard if has items.
     *
     * @return void
     */
    private function clearClipboard()
    {
        $basicDefinition = $this->getEnvironment()->getDataDefinition()->getBasicDefinition();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());
        } else {
            $filter->andHasNoParent();
        }

        $items = $this->getEnvironment()->getClipboard()->fetch($filter);
        if (count($items) < 1) {
            return;
        }

        foreach ($items as $item) {
            $this->getEnvironment()->getClipboard()->remove($item);
        }
    }
}
