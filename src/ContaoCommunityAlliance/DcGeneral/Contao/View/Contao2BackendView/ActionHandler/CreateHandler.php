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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
/**
 * Class CreateHandler
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class CreateHandler extends AbstractHandler
{
    /**
     * Handle the action.
     *
     * @return void
     */
    public function process()
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        $event         = $this->getEvent();
        $action        = $event->getAction();

        // Only handle if we does not have a manual sorting or we know where to insert.
        // Manual sorting is handled by clipboard.
        if ($action->getName() !== 'create'
            || (ViewHelpers::getManualSortingProperty($environment) && !$inputProvider->hasParameter('after'))) {
            return;
        }

        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->getEmptyModel();
        $clone        = $dataProvider->getEmptyModel();

        $view = $environment->getView();
        if (!$view instanceof BaseView) {
            return;
        }

        $editMask = new EditMask($view, $model, $clone, null, null, $view->breadcrumb());
        $event->setResponse($editMask->execute());
    }
}
