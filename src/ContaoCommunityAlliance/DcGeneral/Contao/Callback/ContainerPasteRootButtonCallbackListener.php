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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;

/**
 * Class ContainerPasteRootButtonCallbackListener.
 *
 * Handler for the paste into root buttons.
 *
 * @package DcGeneral\Contao\Callback
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
            $event->getEnvironment()->getClipboard()->getContainedIds(),
            null,
            null
        );
    }

    /**
     * Set the HTML code for the button.
     *
     * @param GetPasteRootButtonEvent $event The event being emitted.
     *
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
