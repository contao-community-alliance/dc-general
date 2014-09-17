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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;

/**
 * Class ContainerPasteButtonCallbackListener.
 *
 * Invoker for paste_button_callbacks.
 *
 * @package DcGeneral\Contao\Callback
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
        return array(
            new DcCompat($event->getEnvironment(), $event->getModel()),
            $event->getModel()->getPropertiesAsArray(),
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getCircularReference(),
            $event->getEnvironment()->getClipboard()->getContainedIds(),
            $event->getPrevious() ? $event->getPrevious()->getId() : null,
            $event->getNext() ? $event->getNext()->getId() : null
        );
    }

    /**
     * Set the HTML code for the button.
     *
     * @param GetPasteButtonEvent $event The event being emitted.
     *
     * @param string              $value The value returned by the callback.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if (is_null($value))
        {
            return;
        }

        $event->setHtml($value);
        $event->stopPropagation();
    }
}
