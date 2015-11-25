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
