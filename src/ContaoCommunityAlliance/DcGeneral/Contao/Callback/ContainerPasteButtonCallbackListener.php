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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;

/**
 * Class ContainerPasteButtonCallbackListener.
 *
 * Invoker for paste_button_callbacks.
 */
class ContainerPasteButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetPasteButtonEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return [
            new DcCompat($event->getEnvironment(), $event->getModel()),
            $event->getModel()->getPropertiesAsArray(),
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->isCircularReference(),
            $event->getContainedModels(),
            $event->getPrevious() ? $event->getPrevious()->getId() : null,
            $event->getNext() ? $event->getNext()->getId() : null
        ];
    }

    /**
     * Set the HTML code for the button.
     *
     * @param GetPasteButtonEvent $event The event being emitted.
     * @param string              $value The value returned by the callback.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if ($value === null) {
            return;
        }

        $event->setHtml($value);
        $event->stopPropagation();
    }
}
