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

namespace DcGeneral\Factory;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\EventPropagatorInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

class DcGeneralFactory implements DcGeneralFactoryInterface
{
	/**
	 * Create a new factory with basic settings from the environment.
	 * This factory can be used to create a new Container, Environment, DcGeneral with the same base settings as the given environment.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @return DcGeneralFactory
	 */
	static public function deriveEmptyFromEnvironment(EnvironmentInterface $environment)
	{
		$factory = new DcGeneralFactory();
		$factory->setEventPropagator($environment->getEventPropagator());
		$factory->setEnvironmentClassName(get_class($environment));
		$factory->setContainerClassName(get_class($environment->getDataDefinition()));
		return $factory;
	}

	/**
	 * Create a new factory with basic settings and same container name as the given environment is build for.
	 * This factory can be used to create a second Container, Environment, DcGeneral for the same container.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @return DcGeneralFactory
	 */
	static public function deriveFromEnvironment(EnvironmentInterface $environment)
	{
		$factory = static::deriveEmptyFromEnvironment($environment);
		$factory->setContainerName($environment->getDataDefinition()->getName());
		return $factory;
	}

	/**
	 * @var string
	 */
	protected $environmentClassName = 'DcGeneral\DefaultEnvironment';

	/**
	 * @var string
	 */
	protected $containerName;

	/**
	 * @var string
	 */
	protected $containerClassName = 'DcGeneral\DataDefinition\DefaultContainer';

	/**
	 * @var string
	 */
	protected $dcGeneralClassName = 'DcGeneral\DcGeneral';

	/**
	 * @var EventPropagatorInterface
	 */
	protected $eventPropagator = null;

	/**
	 * @var EnvironmentInterface
	 */
	protected $environment = null;

	/**
	 * @var ContainerInterface
	 */
	protected $dataContainer = null;

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironmentClassName($environmentClassName)
	{
		$this->environmentClassName = (string) $environmentClassName;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironmentClassName()
	{
		return $this->environmentClassName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainerName($containerName)
	{
		$this->containerName = (string) $containerName;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerName()
	{
		return $this->containerName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainerClassName($containerClassName)
	{
		$this->containerClassName = (string) $containerClassName;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerClassName()
	{
		return $this->containerClassName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDcGeneralClassName($dcGeneralClassName)
	{
		$this->dcGeneralClassName = (string) $dcGeneralClassName;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDcGeneralClassName()
	{
		return $this->dcGeneralClassName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEventPropagator(EventPropagatorInterface $eventPropagator)
	{
		$this->eventPropagator = $eventPropagator;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEventPropagator()
	{
		return $this->eventPropagator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment(EnvironmentInterface $environment = null)
	{
		$this->environment = $environment;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDataContainer(ContainerInterface $dataContainer = null)
	{
		$this->dataContainer = $dataContainer;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataContainer()
	{
		return $this->dataContainer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createDcGeneral()
	{
		if (empty($this->containerName) && !$this->dataContainer) {
			throw new DcGeneralRuntimeException('Required container name or container is missing');
		}

		if (empty($this->eventPropagator)) {
			throw new DcGeneralRuntimeException('Required event propagator is missing');
		}

		if ($this->environment) {
			$environment = $this->environment;
		}
		else {
			$environment = $this->createEnvironment();
		}

		// create reflections classes at one place
		$dcGeneralClass = new \ReflectionClass($this->dcGeneralClassName);

		/** @var \DcGeneral\DcGeneral $dcGeneral */
		$dcGeneral = $dcGeneralClass->newInstance($environment);

		$event = new CreateDcGeneralEvent($dcGeneral);
		$this->eventPropagator->propagate($event, array($this->containerName));

		return $dcGeneral;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createEnvironment()
	{
		if (empty($this->containerName) && !$this->dataContainer) {
			throw new DcGeneralRuntimeException('Required container name or container is missing');
		}

		if (empty($this->eventPropagator)) {
			throw new DcGeneralRuntimeException('Required event propagator is missing');
		}

		if ($this->dataContainer) {
			$dataContainer = $this->dataContainer;
		}
		else {
			$dataContainer = $this->createContainer();
		}

		$environmentClass = new \ReflectionClass($this->environmentClassName);

		/** @var EnvironmentInterface $environment */
		$environment = $environmentClass->newInstance();
		$environment->setDataDefinition($dataContainer);
		$environment->setEventPropagator($this->eventPropagator);

		$event = new PopulateEnvironmentEvent($environment);
		$this->eventPropagator->propagate($event, array($this->containerName));

		return $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createContainer()
	{
		if (empty($this->containerName)) {
			throw new DcGeneralRuntimeException('Required container name is missing');
		}

		if (empty($this->eventPropagator)) {
			throw new DcGeneralRuntimeException('Required event propagator is missing');
		}

		$containerClass = new \ReflectionClass($this->containerClassName);

		/** @var ContainerInterface $dataContainer */
		$dataContainer = $containerClass->newInstance($this->containerName);

		$event = new BuildDataDefinitionEvent($dataContainer);
		$this->eventPropagator->propagate($event, array($this->containerName));

		return $dataContainer;
	}
}
