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

use DcGeneral\DcGeneral;
use Symfony\Component\EventDispatcher\Event;

class CreateDcGeneralEvent extends Event
{
    const NAME = 'dc-general.factory.create-dc-general';

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
