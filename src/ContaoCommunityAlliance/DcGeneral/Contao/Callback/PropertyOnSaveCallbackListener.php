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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

/**
 * Class PropertyOnSaveCallbackListener.
 *
 * Handler for the save_callbacks of a property.
 *
 * @package DcGeneral\Contao\Callback
 */
class PropertyOnSaveCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event being emitted.
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
	 * @param EncodePropertyValueFromWidgetEvent $event The event being emitted.
	 *
	 * @param mixed                              $value The encoded value.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		$event->setValue($value);
	}
}
