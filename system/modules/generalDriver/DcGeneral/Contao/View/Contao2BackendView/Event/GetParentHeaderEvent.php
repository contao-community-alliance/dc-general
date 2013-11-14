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

namespace DcGeneral\Contao\View\Contao2BackendView\Event;

use DcGeneral\Event\AbstractEnvironmentAwareEvent;

class GetParentHeaderEvent
	extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-parent-header';

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
