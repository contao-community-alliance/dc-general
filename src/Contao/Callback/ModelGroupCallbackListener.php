<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;

/**
 * Class ModelGroupCallbackListener.
 *
 * Handler for the group header callbacks of a property.
 */
class ModelGroupCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetGroupHeaderEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return [
            $event->getGroupField(),
            $event->getGroupingMode(),
            $event->getValue(),
            $event->getModel()->getPropertiesAsArray()
        ];
    }

    /**
     * Set the value in the event.
     *
     * @param GetGroupHeaderEvent $event The event being emitted.
     * @param string              $value The value returned by the callback.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if ($value === null) {
            return;
        }

        $event->setValue($value);
        $event->stopPropagation();
    }
}
