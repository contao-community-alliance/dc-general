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

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class AbstractCallbackListener.
 *
 * Abstract base class for a callback listener.
 *
 * @package DcGeneral\Contao\Callback
 */
abstract class AbstractCallbackListener
{
    /**
     * The callback to use.
     *
     * @var array|callable
     */
    protected $callback;

    /**
     * The data container name to limit this execution to.
     *
     * @var null|string
     */
    protected $dataContainerName;

    /**
     * Create a new instance of the listener.
     *
     * @param array|callable $callback     The callback to call when invoked.
     *
     * @param array|null     $restrictions The restrictions for the callback.
     */
    public function __construct($callback = null, $restrictions = null)
    {
        $this->callback = $callback;

        if ($restrictions) {
            call_user_func_array(array($this, 'setRestrictions'), $restrictions);
        }
    }

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = null)
    {
        $this->dataContainerName = $dataContainerName;
    }

    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param AbstractEnvironmentAwareEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        return (empty($this->dataContainerName)
            || ($event->getEnvironment()->getDataDefinition()->getName() == $this->dataContainerName)
        );
    }

    /**
     * Retrieve the attached callback.
     *
     * @return array|callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Retrieve the arguments for the callback.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event The event being emitted.
     *
     * @return array
     */
    abstract public function getArgs($event);

    /**
     * Invoke the callback.
     *
     * @param AbstractEnvironmentAwareEvent $event The Event for which the callback shall be invoked.
     *
     * @return void
     */
    public function __invoke($event)
    {
        if ($this->getCallback() && $this->wantToExecute($event)) {
            Callbacks::callArgs($this->getCallback(), $this->getArgs($event));
        }
    }
}
