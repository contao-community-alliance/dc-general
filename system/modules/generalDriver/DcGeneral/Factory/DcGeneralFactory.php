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
use DcGeneral\Event\EventPropagator;
use DcGeneral\Event\EventPropagatorInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

class DcGeneralFactory implements DcGeneralFactoryInterface
{
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
	public function createDcGeneral()
	{
		global $container;

		if (empty($this->containerName)) {
			throw new DcGeneralRuntimeException('Required container name is missing');
		}

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];
		$propagator = new EventPropagator($dispatcher);

		// 1. pass: fires BuildDataDefinitionEvent
		$dataContainer = $this->createContainer($propagator);

		// 2. pass: fires PopulateEnvironmentEvent
		$environment = $this->createEnvironment($dataContainer, $propagator);

		// create reflections classes at one place
		$dcGeneralClass = new \ReflectionClass($this->dcGeneralClassName);

		/** @var \DcGeneral\DcGeneral $dcGeneral */
		$dcGeneral = $dcGeneralClass->newInstance($environment);

		$event = new CreateDcGeneralEvent($dcGeneral);
		$propagator->propagate($event, array($this->containerName));

		return $dcGeneral;
	}

	/**
	 *
	 * @param ContainerInterface                        $dataContainer
	 *
	 * @param \DcGeneral\Event\EventPropagatorInterface $propagator
	 *
	 * @return EnvironmentInterface
	 */
	protected function createEnvironment(ContainerInterface $dataContainer, EventPropagatorInterface $propagator)
	{
		$environmentClass = new \ReflectionClass($this->environmentClassName);

		/** @var EnvironmentInterface $environment */
		$environment = $environmentClass->newInstance();
		$environment->setDataDefinition($dataContainer);
		$environment->setEventPropagator($propagator);

		$event = new PopulateEnvironmentEvent($environment);
		$propagator->propagate($event, array($this->containerName));

		return $environment;
	}

	/**
	 * @param \DcGeneral\Event\EventPropagatorInterface $propagator
	 *
	 * @return ContainerInterface
	 */
	protected function createContainer(EventPropagatorInterface $propagator)
	{
		$containerClass = new \ReflectionClass($this->containerClassName);

		/** @var ContainerInterface $dataContainer */
		$dataContainer = $containerClass->newInstance($this->containerName);

		$event = new BuildDataDefinitionEvent($dataContainer);
		$propagator->propagate($event, array($this->containerName));

		return $dataContainer;
	}
}
