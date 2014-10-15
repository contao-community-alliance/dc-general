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

/**
 * Class AbstractReturningPropertyCallbackListener.
 *
 * Abstract base class for a callback listener.
 *
 * @package DcGeneral\Contao\Callback
 */
abstract class AbstractReturningPropertyCallbackListener extends AbstractReturningCallbackListener
{
    protected $dataContainerName;

    protected $propertyName;

    /**
     * Set the restrictions for this callback.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = '', $propertyName = '')
    {
        $this->dataContainerName = $dataContainerName;
        $this->propertyName      = $propertyName;
    }

    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event The Event for which the callback shall be invoked.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function wantToExecute($event)
    {
        return ($event->getEnvironment()->getDataDefinition()->getName() == $this->dataContainerName)
            && ($event->getProperty() == $this->propertyName);
    }
}
