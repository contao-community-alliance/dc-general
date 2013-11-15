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

use DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;

class ModelChildRecordCallbackListener extends AbstractReturningCallbackListener
{
	function __construct($callback)
	{
		parent::__construct($callback);
	}

	/**
	 * @param ParentViewChildRecordEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getModel()->getPropertiesAsArray()
		);
	}

	/**
	 * @param ParentViewChildRecordEvent $event
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
