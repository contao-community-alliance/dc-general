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

namespace DcGeneral\Event;

use DcGeneral\ViewAwareInterface;
use DcGeneral\View\ViewInterface;
use Symfony\Component\EventDispatcher\Event;

class AbstractViewAwareEvent
	extends AbstractEnvironmentAwareEvent
	implements ViewAwareInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getView()
	{
		return $this->getEnvironment()->getView();
	}
}
