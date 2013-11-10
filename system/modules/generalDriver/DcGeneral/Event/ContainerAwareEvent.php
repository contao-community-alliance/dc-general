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

use DcGeneral\ContainerAwareInterface;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\EnvironmentInterface;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEvent
	extends Event
	implements ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Create a new container aware event.
	 * 
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainer()
	{
		return $this->container;
	}
}
