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

class GetBreadcrumbEvent
	extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-breadcrumb';

	/**
	 * @var array
	 */
	protected $elements;

	/**
	 * @param array $elements
	 *
	 * @return $this
	 */
	public function setElements($elements)
	{
		$this->elements = $elements;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getElements()
	{
		return $this->elements;
	}
}
