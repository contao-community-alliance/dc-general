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

use DcGeneral\Contao\View\Contao2BackendView\Event\FormatGroupLabelEvent;

class ModelGroupCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * @param FormatGroupLabelEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getGroupLabel(),
			$event->getMode(),
			$event->getPropertyName(),
			$event->getModel()->getPropertiesAsArray()
		);
	}

	/**
	 * @param FormatGroupLabelEvent $event
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value)) {
			return;
		}

		$event->setGroupLabel($value);
		$event->stopPropagation();
	}
}
