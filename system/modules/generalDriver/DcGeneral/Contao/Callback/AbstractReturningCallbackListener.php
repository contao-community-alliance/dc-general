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
 * Class AbstractReturningCallbackListener.
 *
 * Abstract base class for callbacks that are returning a value.
 *
 * @package DcGeneral\Contao\Callback
 */
abstract class AbstractReturningCallbackListener extends AbstractCallbackListener
{
	/**
	 * Update the values in the event with the value returned by the callback.
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event The event being emitted.
	 *
	 * @param mixed                                    $value The value returned by the callback.
	 *
	 * @return void
	 */
	abstract public function update($event, $value);

	/**
	 * {@inheritdoc}
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
