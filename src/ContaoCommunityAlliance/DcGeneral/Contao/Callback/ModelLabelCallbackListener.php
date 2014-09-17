<?php
/**
 * PHP version 5
 *
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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * Class ModelLabelCallbackListener.
 *
 * Handle the label_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelLabelCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param ModelToLabelEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            $event->getModel()->getPropertiesAsArray(),
            $event->getLabel(),
            new DcCompat($event->getEnvironment(), $event->getModel()),
            $event->getArgs()
        );
    }

    /**
     * Set the value in the event.
     *
     * @param ModelToLabelEvent $event The event being emitted.
     *
     * @param string            $value The label text to use.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if (is_null($value)) {
            return;
        }

        $event->setLabel($value);
    }
}
