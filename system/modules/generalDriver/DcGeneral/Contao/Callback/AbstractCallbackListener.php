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

namespace DcGeneral\Contao\Callbacks;

use DcGeneral\Exception\DcGeneralRuntimeException;
use Symfony\Component\EventDispatcher\Event;

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
	 * @return array
	 */
	abstract public function getArgs(Event $event = null);

	/**
	 * Invoke the callback.
	 */
	public function __invoke(Event $event = null)
	{
		if ($this->getCallback()) {
			Callbacks::callArgs($this->getCallback(), $this->getArgs($event));
		}
	}
}
