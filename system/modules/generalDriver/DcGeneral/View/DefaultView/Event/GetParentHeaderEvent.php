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

namespace DcGeneral\View\DefaultView\Event;

use DcGeneral\Event\EnvironmentAwareEvent;

class GetParentHeaderEvent
	extends EnvironmentAwareEvent
{
	const NAME = 'DcGeneral\View\DefaultView\Event\GetParentHeader';

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
