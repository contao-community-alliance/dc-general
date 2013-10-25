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
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;
use DcGeneral\Factory\Event\CreateEnvironmentEvent;

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

		// 1. pass: fires BuildDataDefinitionEvent
		$dataContainer = $this->createContainer();

		// 2. pass: fires PopulateEnvironmentEvent
		$environment = $this->createEnvironment($dataContainer);

		// create reflections classes at one place
		$dcGeneralClass = new \ReflectionClass($this->dcGeneralClassName);

		$dcGeneral = $dcGeneralClass->newInstance($environment);

		$event = new CreateDcGeneralEvent($dcGeneral);
		$dispatcher->dispatch($event::NAME, $event);

		return $dcGeneral;
	}

	/**
	 *
	 * @param ContainerInterface $dataContainer
	 *
	 * @return EnvironmentInterface
	 */
	protected function createEnvironment(ContainerInterface $dataContainer)
	{
		global $container;

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];

		$environmentClass = new \ReflectionClass($this->environmentClassName);

		/** @var EnvironmentInterface $environment */
		$environment = $environmentClass->newInstance();
		$environment->setDataDefinition($dataContainer);

		$event = new CreateEnvironmentEvent($environment);
		$dispatcher->dispatch($event::NAME, $event);

		return $environment;
	}

	/**
	 * @return ContainerInterface
	 */
	protected function createContainer()
	{
		global $container;

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];

		$containerClass = new \ReflectionClass($this->containerClassName);

		/** @var ContainerInterface $dataContainer */
		$dataContainer = $containerClass->newInstance($this->containerName);

		$event = new BuildDataDefinitionEvent($dataContainer);
		$dispatcher->dispatch($event::NAME, $event);

		return $container;
	}
}
