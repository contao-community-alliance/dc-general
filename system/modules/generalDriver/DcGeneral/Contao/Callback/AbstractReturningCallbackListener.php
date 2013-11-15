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

abstract class AbstractReturningCallbackListener extends AbstractCallbackListener
{
	/**
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 *
	 * @return void
	 */
	abstract public function update($event, $value);

	/**
	 * Invoke the callback.
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 */
	public function __invoke($event)
	{
		if ($this->getCallback()) {
			$this->update(
				$event,
				Callbacks::callArgs($this->getCallback(), $this->getArgs($event))
			);
		}
	}
}
