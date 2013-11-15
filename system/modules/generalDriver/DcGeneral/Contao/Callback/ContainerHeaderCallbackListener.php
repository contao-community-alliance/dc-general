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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use DcGeneral\DC_General;
use DcGeneral\Event\PostDuplicateModelEvent;

class ContainerHeaderCallbackListener extends AbstractReturningCallbackListener
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
	 * @param GetParentHeaderEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getAdditional(), $this->dcGeneral);
	}

	/**
	 * @param GetParentHeaderEvent $event
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value)) {
			return;
		}

		$event->setAdditional($value);
		$event->stopPropagation();
	}
}
