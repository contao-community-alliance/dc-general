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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use DcGeneral\DC_General;

/**
 * Class ContainerPasteButtonCallbackListener.
 *
 * Invoker for paste_button_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerPasteButtonCallbackListener extends AbstractReturningCallbackListener
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
	 * @param GetPasteButtonEvent|GetPasteRootButtonEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$this->dcGeneral,
			$event->getModel()->getPropertiesAsArray(),
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getCircularReference(),
			$event->getEnvironment()->getClipboard()->getContainedIds(),
			$event->getPrevious()->getId(),
			$event->getNext()->getId()
		);
	}

	/**
	 * Set the HTML code for the button.
	 *
	 * @param GetPasteButtonEvent|GetPasteRootButtonEvent $event The event being emitted.
	 *
	 * @param string                                      $value The value returned by the callback.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setHtml($value);
		$event->stopPropagation();
	}
}
