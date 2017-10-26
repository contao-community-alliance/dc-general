<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\BackCommand;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class SelectController.
 *
 * This class handles multiple actions.
 */
class SelectHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Delete action handler.
     *
     * @var DeleteHandler
     */
    private $deleteHandler;

    /**
     * Copy action handler.
     *
     * @var CopyHandler
     */
    private $copyHandler;

    /**
     * SelectHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     * @param DeleteHandler            $deleteHandler     The delete action handler.
     * @param CopyHandler              $copyHandler       The copy action handler.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        DeleteHandler $deleteHandler,
        CopyHandler $copyHandler
    ) {
        $this->setScopeDeterminator($scopeDeterminator);

        $this->deleteHandler = $deleteHandler;
        $this->copyHandler = $copyHandler;
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

        if ($action->getName() !== 'select') {
            return;
        }

        $this->process($action, $event->getEnvironment());
    }

    /**
     * Handle the action.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function process(Action $action, EnvironmentInterface $environment)
    {
        $submitAction = $this->getSubmitAction($environment);

        if (!$submitAction) {
            $this->removeGlobalCommands($environment);

            return;
        }

        $modelIds     = $this->getModelIds($environment, $action, $submitAction);
        $actionMethod = sprintf('handle%sAllAction', ucfirst($submitAction));

        call_user_func(array($this, $actionMethod), $modelIds);
    }

    /**
     * Get the submit action name.
     *
     * @param EnvironmentInterface $environment The environment.
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
     * Remove the global commands by action select.
     *
     * We need the back button only.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function removeGlobalCommands(EnvironmentInterface $environment)
    {
        /** @var Contao2BackendViewDefinitionInterface $view */
        $dataDefinition = $environment->getDataDefinition();
        $view           = $dataDefinition->getDefinition('view.contao2backend');
        $globalCommands = $view->getGlobalCommands();

        foreach ($globalCommands->getCommands() as $globalCommand) {
            if (!($globalCommand instanceof BackCommand)) {
                $globalCommands->removeCommand($globalCommand);
            }
        }
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
     * @param EnvironmentInterface $environment The environment.
     * @param ModelId[]            $modelIds   The list of model ids.
     *
     * @return void
     */
    protected function handleDeleteAllAction(EnvironmentInterface $environment, $modelIds)
    {
        foreach ($modelIds as $modelId) {
            $this->deleteHandler->delete($environment, $modelId);
        }

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Handle the delete all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelId[]            $modelIds    The list of model ids.
     *
     * @return void
     */
    protected function handleCutAllAction(EnvironmentInterface $environment, $modelIds)
    {
        $clipboard   = $environment->getClipboard();
        $parentId    = $this->getParentId($environment);

        foreach ($modelIds as $modelId) {
            $clipboard->push(new Item(Item::CUT, $parentId, $modelId));
        }

        $clipboard->saveTo($environment);

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Handle the delete all action.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface[]   $modelIds    The list of model ids.
     *
     * @return void
     */
    protected function handleCopyAllAction(EnvironmentInterface $environment, $modelIds)
    {
        if (ViewHelpers::getManualSortingProperty($environment)) {
            $clipboard = $environment->getClipboard();
            $parentId  = $this->getParentId($environment);

            foreach ($modelIds as $modelId) {
                $item = new Item(Item::COPY, $parentId, $modelId);

                $clipboard->push($item);
            }

            $clipboard->saveTo($environment);
        } else {
            foreach ($modelIds as $modelId) {
                $this->copyHandler->copy($environment, $modelId);
            }
        }

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Handle the delete all action.
     *
     * @param ModelId[] $modelIds The list of model ids.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Not yet implemented.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function handleOverrideAllAction($modelIds)
    {
        throw new DcGeneralRuntimeException('Action overrideAll is not implemented yet.');
    }

    /**
     * Handle the delete all action.
     *
     * @param ModelId[] $modelIds The list of model ids.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Not yet implemented.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function handleEditAllAction($modelIds)
    {
        throw new DcGeneralRuntimeException('Action editAll is not implemented yet.');
    }

    /**
     * Get the parent model id.
     *
     * Returns null if no parent id is given.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ModelIdInterface|null
     */
    protected function getParentId(EnvironmentInterface $environment)
    {
        $parentIdRaw = $environment->getInputProvider()->getParameter('pid');

        if ($parentIdRaw) {
            $parentId = ModelId::fromSerialized($parentIdRaw);
            return $parentId;
        }

        return null;
    }
}
