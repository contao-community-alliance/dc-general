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
use DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use DcGeneral\DC_General;

/**
 * Class PropertyInputFieldCallbackListener.
 *
 * Handle input_field_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class PropertyInputFieldCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * The DC_General instance.
	 *
	 * @var DC_General
	 */
	protected $dcGeneral;

	/**
	 * Create a new instance of the listener.
	 *
	 * @param array|callable $callback  The callback to call when invoked.
	 *
	 * @param DC_General     $dcGeneral The DC_General instance to use in the callback.
	 */
	public function __construct($callback, DC_General $dcGeneral)
	{
		parent::__construct($callback);
		$this->dcGeneral = $dcGeneral;
	}

	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param BuildWidgetEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getProperty(), new DcCompat($event->getEnvironment(), $event->getModel(), $event->getProperty()));
	}

	/**
	 * Update the widget in the event.
	 *
	 * @param BuildWidgetEvent $event The event being emitted.
	 *
	 * @param \Widget          $value The widget that has been constructed.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		$event->setWidget($value);
	}
}
