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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

/**
 * Class PropertyOnSaveCallbackListener.
 *
 * Handler for the save_callbacks of a property.
 */
class PropertyOnSaveCallbackListener extends AbstractReturningPropertyCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            $event->getValue(),
            new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty())
        );
    }

    /**
     * Update the value in the event.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event being emitted.
     *
     * @param mixed                              $value The encoded value.
     *
     * @return void
     */
    public function update($event, $value)
    {
        $event->setValue($value);
    }
}
