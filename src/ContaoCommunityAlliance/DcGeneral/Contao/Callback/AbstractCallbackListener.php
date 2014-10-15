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
     * Create a new instance of the listener.
     *
     * @param array|callable $callback     The callback to call when invoked.
     *
     * @param array|null     $restrictions The restrictions for the callback.
     */
    public function __construct($callback = null, $restrictions = null)
    {
        $this->callback = $callback;

        call_user_func_array(array($this, 'setRestrictions'), $restrictions);
    }

    /**
     * Set the restrictions for this callback.
     *
     * @return void
     */
    public function setRestrictions()
    {
        // No op.
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
        return true;
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
     * @param \Symfony\Component\EventDispatcher\Event $event The Event for which the callback shall be invoked.
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
