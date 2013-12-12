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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\DC_General;

/**
 * Class ContainerGetBreadcrumbCallbackListener.
 *
 * Callback handler for breadcrumbs in backend.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerGetBreadcrumbCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * The DC_General instance.
	 *
	 * @var \DcGeneral\DC_General
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
	 * @param GetBreadcrumbEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$this->dcGeneral
		);
	}

	/**
	 * Update the information in the event with the list of breadcrumb elements returned by the callback.
	 *
	 * @param GetBreadcrumbEvent $event The event being emitted.
	 *
	 * @param array              $value The breadcrumb elements returned by the callback.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setElements($value);
	}
}
