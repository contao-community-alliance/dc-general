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
     * The name of the operation button to limit execution on.
     *
     * @var null|string
     */
    protected $operationName = null;

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     *
     * @param null|string $operationName     The name of the operation button to limit execution on.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = null, $operationName = null)
    {
        parent::setRestrictions($dataContainerName);
        $this->operationName = $operationName;
    }

    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param GetGlobalButtonEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        return parent::wantToExecute($event)
            && (empty($this->operationName)
                || ($event->getKey() == $this->operationName)
            );
    }

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
        if ($value === null) {
            return;
        }

        $event->setHtml($value);
        $event->stopPropagation();
    }
}
