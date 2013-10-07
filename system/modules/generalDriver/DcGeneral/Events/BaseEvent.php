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

namespace DcGeneral\Events;

use Symfony\Component\EventDispatcher\Event;

class BaseEvent
	extends Event
{
	/**
	 * @var \DcGeneral\EnvironmentInterface
	 */
	protected $environment;

	/**
	 * @param \DcGeneral\EnvironmentInterface $environment
	 *
	 * @return $this
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;

		return $this;
	}

	/**
	 * @return \DcGeneral\EnvironmentInterface
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}
}
