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

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;

/**
 * Class PropertyOnLoadCallbackListener.
 *
 * Handler for the load_callbacks of a property.
 *
 * @extends AbstractReturningPropertyCallbackListener<DecodePropertyValueForWidgetEvent>
 */
class PropertyOnLoadCallbackListener extends AbstractReturningPropertyCallbackListener
{
    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        return [
            $event->getValue(),
            new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty())
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        $event->setValue($value);
    }
}
