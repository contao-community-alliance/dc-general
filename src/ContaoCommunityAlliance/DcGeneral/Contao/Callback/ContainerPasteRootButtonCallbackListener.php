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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;

/**
 * Class ContainerPasteRootButtonCallbackListener.
 *
 * Handler for the paste into root buttons.
 */
class ContainerPasteRootButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetPasteRootButtonEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            new DcCompat($event->getEnvironment()),
            $event->getEnvironment()->getDataProvider()->getEmptyModel()->getPropertiesAsArray(),
            $event->getEnvironment()->getDataDefinition()->getName(),
            false,
            array(),
            null,
            null
        );
    }

    /**
     * Set the HTML code for the button.
     *
     * @param GetPasteRootButtonEvent $event The event being emitted.
     * @param string                  $value The value returned by the callback.
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
