<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller\ActionController;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

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
     * @return mixed
     */
    public function process()
    {
        $action = $this->getEvent()->getAction();

        if ($action->getName() !== 'select') {
            return;
        }

        $environment  = $this->getEvent()->getEnvironment();
        $submitAction = $this->getSubmitAction($environment);

        if (!$submitAction) {
            return;
        }

        $controller   = new ActionController($environment);
        $modelIds     = $this->getModelIds($environment, $action, $submitAction);
        $actionMethod = sprintf('handle%sAllAction', ucfirst($submitAction));

        call_user_func(array($this, $actionMethod), $controller, $modelIds);
    }

    /**
     * Get the submit action name
     *
     * @param EnvironmentInterface $environment The request environment.
     *
     * @return string
     */
    protected function getSubmitAction(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        $actions       = array('delete', 'cut', 'copy', 'override', 'edit');

        foreach ($actions as $action) {
            if ($inputProvider->hasValue($action)) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Get The model ids from the environment.
     *
     * @param EnvironmentInterface $environment  The request environment.
     * @param Action               $action       The dcg action.
     * @param string               $submitAction The submit action name.
     *
     * @return ModelId[]
     */
    protected function getModelIds(EnvironmentInterface $environment, Action $action, $submitAction)
    {
        $modelIds = (array) $environment->getInputProvider()->getValue('IDS');

        if (!empty($modelIds)) {
            $modelIds = array_map(
                function ($modelId) {
                    return ModelId::fromSerialized($modelId);
                },
                $modelIds
            );

            $event = new PrepareMultipleModelsActionEvent($environment, $action, $modelIds, $submitAction);
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);

            $modelIds = $event->getModelIds();
        }

        return $modelIds;
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param ModelId[]        $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleDeleteAllAction(ActionController $controller, $modelIds)
    {
        foreach ($modelIds as $modelId) {
            // TODO: How to handle errors for one item? Abort and roll back or just log it and print the messages?
            $controller->delete($modelId);
        }

        ViewHelpers::redirectHome($controller->getEnvironment());
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param array            $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleCutAllAction(ActionController $controller, $modelIds)
    {
        $environment = $controller->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        $clipboard   = $environment->getClipboard();
        $parentId    = $this->getParentId();

        // TODO: Protect against cut in no tree and no manual sorting view.

        foreach ($modelIds as $modelId) {
            $clipboard->push(new Item(Item::COPY, $parentId, $modelId));
        }

        $clipboard->saveTo($controller->getEnvironment());

        $event = new GetReferrerEvent();
        $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $event);

        $event = new RedirectEvent($event->getReferrerUrl());
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $event);
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController   $controller The action controller.
     * @param ModelIdInterface[] $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleCopyAllAction(ActionController $controller, $modelIds)
    {
        if (ViewHelpers::getManualSortingProperty($this->getEnvironment())) {
            $clipboard = $this->getEnvironment()->getClipboard();
            $parentId  = $this->getParentId();

            foreach ($modelIds as $modelId) {
                $item = new Item(Item::COPY, $parentId, $modelId);

                $clipboard->push($item);
            }

            $clipboard->saveTo($this->getEnvironment());
        } else {
            $processor = $this->createCopyProcessor();

            foreach ($modelIds as $modelId) {
                $controller->copy($modelId, $processor);
            }
        }

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param array            $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleOverrideAllAction(ActionController $controller, $modelIds)
    {
        throw new DcGeneralRuntimeException('Action overrideAll is not implemented yet.');
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param array            $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleEditAllAction(ActionController $controller, $modelIds)
    {
        throw new DcGeneralRuntimeException('Action editAll is not implemented yet.');
    }

    /**
     * Get the parent model id.
     *
     * Returns null if no parent id is given.
     *
     * @return ModelIdInterface|null
     */
    protected function getParentId()
    {
        $parentIdRaw = $this->getEnvironment()->getInputProvider()->getParameter('pid');

        if ($parentIdRaw) {
            $parentId = ModelId::fromSerialized($parentIdRaw);
            return $parentId;
        }

        return null;
    }

    /**
     * Create the copy processor.
     *
     * @return callable
     */
    protected function createCopyProcessor()
    {
        $environment = $this->getEnvironment();
        $processor   = function (
            ModelInterface $copyModel,
            ModelInterface $model,
            $preFunction,
            $postFunction
        ) use ($environment) {
            call_user_func_array($preFunction, $environment, $copyModel, $model);

            $provider = $environment->getDataProvider($copyModel->getProviderName());
            $provider->save($copyModel);

            call_user_func_array($postFunction, $environment, $copyModel, $model);
        };
        return $processor;
    }
}
