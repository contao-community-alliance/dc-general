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
use DcGeneral\Event\PostDeleteModelEvent;

class ContainerOnDeleteCallbackListener extends AbstractCallbackListener
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
	 * @param PostDeleteModelEvent $event
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		// TODO find a way to get tl_undo record ID here
		return array($this->dcGeneral, 0);
	}
}
