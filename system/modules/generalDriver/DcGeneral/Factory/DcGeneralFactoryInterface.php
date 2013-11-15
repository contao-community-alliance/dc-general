<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Factory;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DcGeneral;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\EventPropagatorInterface;

interface DcGeneralFactoryInterface
{
	/**
	 * @param string $environmentClassName
	 */
	public function setEnvironmentClassName($environmentClassName);

	/**
	 * @return string
	 */
	public function getEnvironmentClassName();

	/**
	 * @param string $containerName
	 */
	public function setContainerName($containerName);

	/**
	 * @return string
	 */
	public function getContainerName();

	/**
	 * @param string $containerClassName
	 */
	public function setContainerClassName($containerClassName);

	/**
	 * @return string
	 */
	public function getContainerClassName();

	/**
	 * @param string $dcGeneralClassName
	 */
	public function setDcGeneralClassName($dcGeneralClassName);

	/**
	 * @return string
	 */
	public function getDcGeneralClassName();

	/**
	 * @param EventPropagatorInterface $eventPropagator
	 */
	public function setEventPropagator(EventPropagatorInterface $eventPropagator);

	/**
	 * @return EventPropagatorInterface
	 */
	public function getEventPropagator();

	/**
	 * @param EnvironmentInterface $environment
	 */
	public function setEnvironment(EnvironmentInterface $environment = null);

	/**
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * @param ContainerInterface $dataContainer
	 */
	public function setDataContainer(ContainerInterface $dataContainer = null);

	/**
	 * @return ContainerInterface
	 */
	public function getDataContainer();

	/**
	 * Create a new instance of DcGeneral.
	 * If no environment is given, a new one is created.
	 *
	 * @return DcGeneral
	 */
	public function createDcGeneral();

	/**
	 * Create a new instance of Environment.
	 * If no container is given, a new one is created.
	 *
	 * @return EnvironmentInterface
	 */
	public function createEnvironment();

	/**
	 * Create a new instance of Container.
	 *
	 * @return ContainerInterface
	 */
	public function createContainer();
}
