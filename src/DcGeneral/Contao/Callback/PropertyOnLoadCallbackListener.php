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

use DcGeneral\Contao\Compatibility\DcCompat;
use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;

/**
 * Class PropertyOnLoadCallbackListener.
 *
 * Handler for the load_callbacks of a property.
 *
 * @package DcGeneral\Contao\Callback
 */
class PropertyOnLoadCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getValue(), new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty()));
	}

	/**
	 * Update the value in the event.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event being emitted.
	 *
	 * @param mixed                             $value The decoded value.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		$event->setValue($value);
	}
}
