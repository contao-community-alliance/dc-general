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

class PropertyInputFieldGetWizardCallbackListener extends AbstractReturningCallbackListener
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
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getWidget(), $event->getProperty(), $this->dcGeneral);
	}

	/**
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent $event
	 *
	 * @param \Widget                                                          $value
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		$event->getWidget()->wizard = $value;
	}
}
