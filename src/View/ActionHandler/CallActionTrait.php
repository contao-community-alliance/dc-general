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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;

/**
 * Trait that calls a dc-general action.
 *
 * @package ContaoCommunityAlliance\DcGeneral\View\ActionHandler
 */
trait CallActionTrait
{
    /**
     * Call a dc-general action (sub processing) and return the result as string.
     *
     * @param EnvironmentInterface $environment Dc general environment.
     * @param string               $actionName  The action name.
     * @param array                $arguments   The optional action arguments.
     *
     * @return string|null
     */
    protected function callAction(EnvironmentInterface $environment, $actionName, $arguments = [])
    {
        $event = new ActionEvent($environment, new Action($actionName, $arguments));
        if (null !== $dispatcher = $environment->getEventDispatcher()) {
            $dispatcher->dispatch($event, DcGeneralEvents::ACTION);
        }

        return $event->getResponse();
    }
}
