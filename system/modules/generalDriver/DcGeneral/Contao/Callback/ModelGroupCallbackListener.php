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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;

/**
 * Class ModelGroupCallbackListener.
 *
 * Handler for the group header callbacks of a property.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelGroupCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param GetGroupHeaderEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getGroupField(),
			$event->getSortingMode(),
			$event->getValue(),
			$event->getModel()->getPropertiesAsArray()
		);
	}

	/**
	 * Set the value in the event.
	 *
	 * @param GetGroupHeaderEvent $event The event being emitted.
	 *
	 * @param string              $value The value returned by the callback.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setValue($value);
		$event->stopPropagation();
	}
}
