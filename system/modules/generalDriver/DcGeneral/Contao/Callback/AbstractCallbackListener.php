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

namespace DcGeneral\Contao\Callback;

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
	 * @param array|callable $callback The callback to call when invoked.
	 */
	public function __construct($callback = null)
	{
		$this->callback = $callback;
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
		if ($this->getCallback())
		{
			Callbacks::callArgs($this->getCallback(), $this->getArgs($event));
		}
	}
}
