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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

class ContainerGlobalButtonCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * @param GetGlobalButtonEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getHref(),
			$event->getLabel(),
			$event->getTitle(),
			$event->getClass(),
			$event->getAttributes(),
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getEnvironment()->getRootIds()
		);
	}

	/**
	 * @param GetGlobalButtonEvent $event
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
