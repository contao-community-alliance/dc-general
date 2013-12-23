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
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract base class for container aware events.
 *
 * This class solely implements the ContainerAwareInterface.
 *
 * @package DcGeneral\Event
 */
abstract class AbstractContainerAwareEvent
	extends Event
	implements ContainerAwareInterface
{
	/**
	 * The container in use.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Create a new container aware event.
	 * 
	 * @param ContainerInterface $container The container in use.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainer()
	{
		return $this->container;
	}
}
