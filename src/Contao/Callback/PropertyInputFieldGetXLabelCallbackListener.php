<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;

/**
 * Class PropertyInputFieldGetXLabelCallbackListener.
 *
 * Handle the property wizard callbacks.
 *
 * @extends AbstractReturningPropertyCallbackListener<BuildWidgetEvent>
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class PropertyInputFieldGetXLabelCallbackListener extends AbstractReturningPropertyCallbackListener
{
    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        return [
            new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty()->getName())
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        $widget = $event->getWidget();
        if ($widget instanceof Widget) {
            $widget->xlabel .= $value;
        }
    }
}
