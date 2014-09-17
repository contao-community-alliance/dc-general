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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

/**
 * Class ContainerGlobalButtonCallbackListener.
 *
 * Handler for the global buttons.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerGlobalButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetGlobalButtonEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            $event->getHref(),
            $event->getLabel(),
            $event->getTitle(),
            $event->getClass(),
            $event->getAttributes(),
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getEnvironment()->getDataDefinition()->getBasicDefinition()->getRootEntries()
        );
    }

    /**
     * Update the event with the information returned by the callback.
     *
     * @param GetGlobalButtonEvent $event The event being emitted.
     *
     * @param string               $value The HTML representation of the button.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if (is_null($value)) {
            return;
        }

        $event->setHtml($value);
        $event->stopPropagation();
    }
}
