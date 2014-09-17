<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;

/**
 * Class PropertyInputFieldCallbackListener.
 *
 * Handle input_field_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class PropertyInputFieldCallbackListener extends AbstractReturningCallbackListener
{

    /**
     * Retrieve the arguments for the callback.
     *
     * @param BuildWidgetEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            $event->getProperty(),
            new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty())
        );
    }

    /**
     * Update the widget in the event.
     *
     * @param BuildWidgetEvent $event The event being emitted.
     *
     * @param \Widget          $value The widget that has been constructed.
     *
     * @return void
     */
    public function update($event, $value)
    {
        $event->setWidget($value);
    }
}
