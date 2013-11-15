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

abstract class AbstractCallbackListener
{
	/**
	 * @var array|callable
	 */
	protected $callback;

	/**
	 * @param array|callable $callback
	 */
	function __construct($callback = null)
	{
		$this->callback = $callback;
	}

	/**
	 * @return array|callable
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 *
	 * @return array
	 */
	abstract public function getArgs($event);

	/**
	 * Invoke the callback.
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 */
	public function __invoke($event)
	{
		if ($this->getCallback()) {
			Callbacks::callArgs($this->getCallback(), $this->getArgs($event));
		}
	}
}
