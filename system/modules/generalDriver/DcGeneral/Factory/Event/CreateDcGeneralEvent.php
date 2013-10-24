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

namespace DcGeneral\Factory\Event;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DcGeneral;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Events\BaseEvent;

class CreateDcGeneralEvent extends BaseEvent
{
	const NAME = 'DcGeneral\Factory\Event\CreateDcGeneral';

	protected $dcGeneral;

	public function __construct(DcGeneral $dcGeneral)
	{
		$this->dcGeneral = $dcGeneral;
	}

	/**
	 * @return \DcGeneral\DcGeneral
	 */
	public function getDcGeneral()
	{
		return $this->dcGeneral;
	}
}
