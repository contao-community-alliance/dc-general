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

use DcGeneral\EnvironmentAwareInterface;
use DcGeneral\EnvironmentInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractEnvironmentAwareEvent
	extends Event
	implements EnvironmentAwareInterface
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * Create a new environment aware event.
	 * 
	 * @param EnvironmentInterface $environment
	 */
	public function __construct(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}
}
