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
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\DC_General;

/**
 * Class ModelLabelCallbackListener.
 *
 * Handle the label_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelLabelCallbackListener extends AbstractReturningCallbackListener
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
	 * @param ModelToLabelEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getModel()->getPropertiesAsArray(),
			$event->getLabel(),
			new DcCompat($event->getEnvironment(), $event->getModel()),
			$event->getArgs()
		);
	}

	/**
	 * Set the value in the event.
	 *
	 * @param ModelToLabelEvent $event The event being emitted.
	 *
	 * @param string            $value The label text to use.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setLabel($value);
	}
}
