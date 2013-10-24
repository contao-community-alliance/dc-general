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
use DcGeneral\Factory\Event\CreateContainerEvent;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;

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

		$environment = $this->createEnvironment();
		$this->createContainer($environment);

		// create reflections classes at one place
		$dcGeneralClass = new \ReflectionClass($this->dcGeneralClassName);

		$dcGeneral = $dcGeneralClass->newInstance($environment);

		$event = new CreateDcGeneralEvent($dcGeneral);
		$dispatcher->dispatch($event::NAME, $event);

		return $dcGeneral;
	}

	/**
	 * @return EnvironmentInterface
	 */
	protected function createEnvironment()
	{
		global $container;

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];

		$environmentClass = new \ReflectionClass($this->environmentClassName);

		$environment = $environmentClass->newInstance();

		$event = new CreateEnvironmentEvent($environment);
		$dispatcher->dispatch($event::NAME, $event);

		return $environment;
	}

	/**
	 * @return ContainerInterface
	 */
	protected function createContainer(EnvironmentInterface $environment)
	{
		global $container;

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];

		$containerClass = new \ReflectionClass($this->containerClassName);

		$container = $containerClass->newInstance($this->containerName);

		$environment->setDataDefinition($container);

		$event = new CreateContainerEvent($environment);
		$dispatcher->dispatch($event::NAME, $event);

		return $container;
	}
}
