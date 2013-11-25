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

class ModelGroupCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * @param GetGroupHeaderEvent $event
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
	 * @param GetGroupHeaderEvent $event
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value)) {
			return;
		}

		$event->setValue($value);
		$event->stopPropagation();
	}
}
