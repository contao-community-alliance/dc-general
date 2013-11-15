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

use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\DC_General;

class ModelLabelCallbackListener extends AbstractReturningCallbackListener
{
	protected $dcGeneral;

	function __construct($callback, DC_General $dcGeneral)
	{
		parent::__construct($callback);
		$this->dcGeneral = $dcGeneral;
	}

	/**
	 * @param ModelToLabelEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getModel()->getPropertiesAsArray(),
			$event->getLabel(),
			$this->dcGeneral,
			$event->getArgs()
		);
	}

	/**
	 * @param ModelToLabelEvent $event
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value)) {
			return;
		}

		$event->setArgs($value);
	}
}
