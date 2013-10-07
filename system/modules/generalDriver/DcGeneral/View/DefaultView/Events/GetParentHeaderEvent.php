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

namespace DcGeneral\View\DefaultView\Events;

use DcGeneral\Events\BaseEvent;

class GetParentHeaderEvent
	extends BaseEvent
{
	const NAME = 'DcGeneral\View\DefaultView\Events\GetParentHeader';

	/**
	 * @var array
	 */
	protected $additional;

	/**
	 * @param array $additional
	 *
	 * @return $this
	 */
	public function setAdditional($additional)
	{
		$this->additional = $additional;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAdditional()
	{
		return $this->additional;
	}
}
