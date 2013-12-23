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

use DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a data definition is being built.
 *
 * @package DcGeneral\Factory\Event
 */
class BuildDataDefinitionEvent extends Event
{
	const NAME = 'dc-general.factory.build-data-definition';

	/**
	 * The data definition container being built.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Create a new instance.
	 *
	 * @param ContainerInterface $container The container being built.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Retrieve the data definition container.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}
}
