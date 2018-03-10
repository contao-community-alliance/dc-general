<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Factory to create a DcGeneral instance.
 */
class DcGeneralFactory implements DcGeneralFactoryInterface
{
    /**
     * Create a new factory with basic settings from the environment.
     *
     * This factory can be used to create a new Container, Environment, DcGeneral with the same base settings as the
     * given environment.
     *
     * @param EnvironmentInterface $environment The environment to use as base.
     *
     * @return DcGeneralFactory
     */
    public static function deriveEmptyFromEnvironment(EnvironmentInterface $environment)
    {
        $factory = new DcGeneralFactory();
        $factory->setEventDispatcher($environment->getEventDispatcher());
        $factory->setTranslator($environment->getTranslator());
        $factory->setEnvironmentClassName(\get_class($environment));
        $factory->setContainerClassName(\get_class($environment->getDataDefinition()));
        return $factory;
    }

    /**
     * Create a new factory with basic settings and same container name as the given environment is build for.
     *
     * This factory can be used to create a second Container, Environment, DcGeneral for the same container.
     *
     * @param EnvironmentInterface $environment The environment to use as base.
     *
     * @return DcGeneralFactory
     */
    public static function deriveFromEnvironment(EnvironmentInterface $environment)
    {
        $factory = static::deriveEmptyFromEnvironment($environment);
        $factory->setContainerName($environment->getDataDefinition()->getName());
        return $factory;
    }

    /**
     * The class name to use for the environment.
     *
     * @var string
     */
    protected $environmentClassName = DefaultEnvironment::class;

    /**
     * The name of the data container.
     *
     * @var string
     */
    protected $containerName;

    /**
     * The class name of the class to use for the data definition container.
     *
     * @var string
     */
    protected $containerClassName = DefaultContainer::class;

    /**
     * The class name of the class to use as DcGeneral.
     *
     * @var string
     */
    protected $dcGeneralClassName = DcGeneral::class;

    /**
     * The event dispatcher to use.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The translator that shall be used.
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * The environment for the new instance.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The data definition container instance.
     *
     * @var ContainerInterface
     */
    protected $dataContainer;

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
    public function setEventDispatcher($dispatcher)
    {
        $this->eventDispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator()
    {
        return $this->translator;
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
     *
     * @throws DcGeneralRuntimeException When no container name, no container or no event propagator is given.
     */
    public function createDcGeneral()
    {
        if (empty($this->containerName) && !$this->dataContainer) {
            throw new DcGeneralRuntimeException('Required container name or container is missing');
        }

        if (empty($this->eventDispatcher)) {
            throw new DcGeneralRuntimeException('Required event dispatcher is missing');
        }

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(PreCreateDcGeneralEvent::NAME, new PreCreateDcGeneralEvent($this));

        if ($this->environment) {
            $environment = $this->environment;
        } else {
            $environment = $this->createEnvironment();
        }

        // Create reflections classes at one place.
        $dcGeneralClass = new \ReflectionClass($this->dcGeneralClassName);

        /** @var DcGeneral $dcGeneral */
        $dcGeneral = $dcGeneralClass->newInstance($environment);

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(CreateDcGeneralEvent::NAME, new CreateDcGeneralEvent($dcGeneral));

        return $dcGeneral;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When no container name, no container, no event propagator or no translator
     *                                   is given.
     */
    public function createEnvironment()
    {
        if (empty($this->containerName) && !$this->dataContainer) {
            throw new DcGeneralRuntimeException('Required container name or container is missing');
        }

        if (empty($this->eventDispatcher)) {
            throw new DcGeneralRuntimeException('Required event dispatcher is missing');
        }

        if (empty($this->translator)) {
            throw new DcGeneralRuntimeException('Required translator is missing');
        }

        if ($this->dataContainer) {
            $dataContainer = clone $this->dataContainer;
        } else {
            $dataContainer = $this->createContainer();
        }

        $environmentClass = new \ReflectionClass($this->environmentClassName);

        /** @var EnvironmentInterface $environment */
        $environment = $environmentClass->newInstance();
        $environment->setDataDefinition($dataContainer);
        $environment->setEventDispatcher($this->eventDispatcher);
        $environment->setTranslator($this->translator);

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(
            PopulateEnvironmentEvent::NAME,
            new PopulateEnvironmentEvent($environment)
        );

        return $environment;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When no container name or no event propagator is given.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function createContainer()
    {
        if (empty($this->containerName)) {
            throw new DcGeneralRuntimeException('Required container name is missing');
        }

        if (empty($this->eventDispatcher)) {
            throw new DcGeneralRuntimeException('Required event dispatcher is missing');
        }

        /** @var DataDefinitionContainerInterface $definitions */
        $definitions = $GLOBALS['container']['dc-general.data-definition-container'];

        if ($definitions->hasDefinition($this->containerName)) {
            return clone $definitions->getDefinition($this->containerName);
        }

        $containerClass = new \ReflectionClass($this->containerClassName);

        /** @var ContainerInterface $dataContainer */
        $dataContainer = $containerClass->newInstance($this->containerName);

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(
            BuildDataDefinitionEvent::NAME,
            new BuildDataDefinitionEvent($dataContainer)
        );

        $definitions->setDefinition($this->containerName, $dataContainer);

        return clone $dataContainer;
    }
}
