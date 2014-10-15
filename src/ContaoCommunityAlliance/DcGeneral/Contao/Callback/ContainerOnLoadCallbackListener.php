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
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;

/**
 * Class ContainerOnLoadCallbackListener.
 *
 * Handle onload_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerOnLoadCallbackListener extends AbstractCallbackListener
{
    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param CreateDcGeneralEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        return (empty($this->dataContainerName)
            || ($event->getDcGeneral()->getEnvironment()->getDataDefinition()->getName() == $this->dataContainerName)
        );
    }

    /**
     * Retrieve the arguments for the callback.
     *
     * @param CreateDcGeneralEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            new DcCompat($event->getDcGeneral()->getEnvironment())
        );
    }
}
