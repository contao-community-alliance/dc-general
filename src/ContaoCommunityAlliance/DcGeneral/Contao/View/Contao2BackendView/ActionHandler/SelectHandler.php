<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\PrepareMultipleModelsActionEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
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
        $action = $this->getEvent()->getAction();

        if ($action->getName() !== 'select') {
            return;
        }

        $submitAction = $this->getSubmitAction();

        if (!$submitAction) {
            return;
        }

        $modelIds     = $this->getModelIds($action, $submitAction);
        $actionMethod = sprintf('handle%sAllAction', ucfirst($submitAction));

        call_user_func(array($this, $actionMethod), $modelIds);
    }

    /**
     * Get the submit action name.
     *
     * @return string
     */
    protected function getSubmitAction()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();
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
     * @param Action $action       The dcg action.
     * @param string $submitAction The submit action name.
     *
     * @return ModelId[]
     */
    protected function getModelIds(Action $action, $submitAction)
    {
        $environment = $this->getEnvironment();
        $modelIds    = (array) $environment->getInputProvider()->getValue('IDS');

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
     * @param ModelId[] $modelIds The list of model ids.
     *
     * @return void
     */
    protected function handleDeleteAllAction($modelIds)
    {
        $handler = new DeleteHandler();
        $handler->setEnvironment($this->getEnvironment());

        foreach ($modelIds as $modelId) {
            $handler->delete($modelId);
        }

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * Handle the delete all action.
     *
     * @param ModelId[] $modelIds The list of model ids.
     *
     * @return void
     */
    protected function handleCutAllAction($modelIds)
    {
        $environment = $this->getEnvironment();
        $clipboard   = $environment->getClipboard();
        $parentId    = $this->getParentId();

        foreach ($modelIds as $modelId) {
            $clipboard->push(new Item(Item::CUT, $parentId, $modelId));
        }

        $clipboard->saveTo($environment);

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * Handle the delete all action.
     *
     * @param ModelIdInterface[] $modelIds The list of model ids.
     *
     * @return void
     */
    protected function handleCopyAllAction($modelIds)
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
            $handler = new CopyHandler();
            $handler->setEnvironment($this->getEnvironment());

            foreach ($modelIds as $modelId) {
                $handler->copy($modelId);
            }
        }

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * Handle the delete all action.
     *
     * @param ModelId[] $modelIds The list of model ids.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Not yet implemented.
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
}
