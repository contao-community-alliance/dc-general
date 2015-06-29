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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SelectController.
 *
 * This class handles multiple actions.
 */
class SelectController implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DcGeneralEvents::ACTION => 'handleAction'
        );
    }

    /**
     * Get the submit action name
     *
     * @param EnvironmentInterface $environment The request environment.
     *
     * @return string
     */
    private function getSubmitAction(EnvironmentInterface $environment)
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
    private function getModelIds(EnvironmentInterface $environment, Action $action, $submitAction)
    {
        $modelIds = (array) $environment->getInputProvider()->getValue('IDS');

        if (! empty($modelIds)) {
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
     * Handle the action event for the select action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleAction(ActionEvent $event)
    {
        if ($event->getAction()->getName() !== 'select') {
            return;
        }

        $action       = $event->getAction();
        $environment  = $event->getEnvironment();
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
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param ModelId[]        $modelIds   The list of model ids.
     *
     * @return void
     */
    private function handleDeleteAllAction(ActionController $controller, $modelIds)
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
    private function handleCutAllAction(ActionController $controller, $modelIds)
    {
        throw new DcGeneralRuntimeException('Action cutAll is not implemented yet.');
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param array            $modelIds   The list of model ids.
     *
     * @return void
     */
    private function handleCopyAllAction(ActionController $controller, $modelIds)
    {
        throw new DcGeneralRuntimeException('Action copyAll is not implemented yet.');
    }

    /**
     * Handle the delete all action.
     *
     * @param ActionController $controller The action controller.
     * @param array            $modelIds   The list of model ids.
     *
     * @return void
     */
    private function handleOverrideAllAction(ActionController $controller, $modelIds)
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
    private function handleEditAllAction(ActionController $controller, $modelIds)
    {
        throw new DcGeneralRuntimeException('Action editAll is not implemented yet.');
    }
}
