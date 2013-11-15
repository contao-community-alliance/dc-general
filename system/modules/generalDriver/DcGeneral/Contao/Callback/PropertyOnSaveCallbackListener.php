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

use DcGeneral\DC_General;

class PropertyOnSaveCallbackListener extends AbstractCallbackListener
{
	/**
	 * @var DC_General
	 */
	protected $dcGeneral;

	function __construct($callback, DC_General $dcGeneral)
	{
		parent::__construct($callback);
		$this->dcGeneral = $dcGeneral;
	}

	/**
	 * Invoke the callback.
	 *
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent $event
	 */
	public function __invoke($event)
	{
		if ($this->getCallback())
		{
			$event->setValue(Callbacks::callArgs($this->getCallback(), $this->getArgs($event)));
		}
	}

	/**
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getValue(), $this->dcGeneral);
	}
}
