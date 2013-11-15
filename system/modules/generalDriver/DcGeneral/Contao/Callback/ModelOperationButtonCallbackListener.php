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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;

class ModelOperationButtonCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * @param GetOperationButtonEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		$attributes = $event->getAttributes();

		return array(
			$event->getModel(),
			$event->getHref(),
			$event->getLabel(),
			$event->getTitle(),
			isset($attributes['icon']) ? $attributes['icon'] : null,
			$event->getAttributes(),
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getEnvironment()->getRootIds(),
			$event->getChildRecordIds(),
			$event->getCircularReference(),
			$event->getPrevious(),
			$event->getNext()
		);
	}

	/**
	 * @param GetOperationButtonEvent $event
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value)) {
			return;
		}

		$event->setHtml($value);
		$event->stopPropagation();
	}
}
