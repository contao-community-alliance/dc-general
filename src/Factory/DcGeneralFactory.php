<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Cache\Factory\DcGeneralFactoryCache;
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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Factory to create a DcGeneral instance.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DcGeneralFactory implements DcGeneralFactoryInterface
{
    /**
     * The cache.
     *
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * The constructor.
     *
     * @param CacheInterface|null $cache The cache.
     */
    public function __construct(CacheInterface $cache = null)
    {
        if (null === $cache) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'You should pass an instance of ' . CacheInterface::class . ' .',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            /** @psalm-suppress DeprecatedClass */
            $cache = System::getContainer()->get(DcGeneralFactoryCache::class);

            assert($cache instanceof CacheInterface);
        }
        $this->cache = $cache;
    }

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
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $factory = new DcGeneralFactory();
        $factory->setEventDispatcher($dispatcher);
        $factory->setTranslator($translator);
        $factory->setEnvironmentClassName(\get_class($environment));
        $factory->setContainerClassName(\get_class($definition));
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

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $factory->setContainerName($definition->getName());
        return $factory;
    }

    /**
     * The class name to use for the environment.
     *
     * @var class-string<EnvironmentInterface>
     */
    protected $environmentClassName = DefaultEnvironment::class;

    /**
     * The name of the data container.
     *
     * @var string
     */
    protected $containerName = '';

    /**
     * The class name of the class to use for the data definition container.
     *
     * @var class-string<ContainerInterface>
     */
    protected $containerClassName = DefaultContainer::class;

    /**
     * The class name of the class to use as DcGeneral.
     *
     * @var class-string<DcGeneral>
     */
    protected $dcGeneralClassName = DcGeneral::class;

    /**
     * The event dispatcher to use.
     *
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher = null;

    /**
     * The translator that shall be used.
     *
     * @var TranslatorInterface|null
     */
    protected $translator = null;

    /**
     * The environment for the new instance.
     *
     * @var EnvironmentInterface|null
     */
    protected $environment = null;

    /**
     * The data definition container instance.
     *
     * @var ContainerInterface|null
     */
    protected $dataContainer = null;

    /**
     * {@inheritdoc}
     */
    public function setEnvironmentClassName($environmentClassName)
    {
        $this->environmentClassName = $environmentClassName;

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
        $this->containerName = $containerName;

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
        $this->containerClassName = $containerClassName;

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
        $this->dcGeneralClassName = $dcGeneralClassName;

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
        if (null === $this->eventDispatcher) {
            throw new DcGeneralRuntimeException('Required event dispatcher is missing');
        }

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
        if (null === $this->translator) {
            throw new DcGeneralRuntimeException('Required translator is missing');
        }

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

        if (null === $this->eventDispatcher) {
            throw new DcGeneralRuntimeException('Required event dispatcher is missing');
        }

        $cacheKey = \md5('dc-general.' . $this->containerName);
        $cache    = $this->cache;
        assert($cache instanceof CacheInterface);

        return $cache->get($cacheKey, function (): DcGeneral {
            // Backwards compatibility.
            $this->getEventDispatcher()->dispatch(new PreCreateDcGeneralEvent($this), PreCreateDcGeneralEvent::NAME);

            $environment = $this->environment ?: $this->createEnvironment();

            $dcGeneral = (new \ReflectionClass($this->dcGeneralClassName))->newInstance($environment);

            // Backwards compatibility.
            $this->getEventDispatcher()->dispatch(new CreateDcGeneralEvent($dcGeneral), CreateDcGeneralEvent::NAME);

            return $dcGeneral;
        });
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

        if ($this->dataContainer) {
            $dataContainer = clone $this->dataContainer;
        } else {
            $dataContainer = $this->createContainer();
        }

        $environment = (new \ReflectionClass($this->environmentClassName))->newInstance();
        $environment->setDataDefinition($dataContainer);
        $environment->setEventDispatcher($this->getEventDispatcher());
        $environment->setTranslator($this->getTranslator());

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(
            new PopulateEnvironmentEvent($environment),
            PopulateEnvironmentEvent::NAME
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

        /** @var DataDefinitionContainerInterface $definitions */
        $definitions = System::getContainer()->get('cca.dc-general.data-definition-container');
        assert($definitions instanceof DataDefinitionContainerInterface);

        if ($definitions->hasDefinition($this->containerName)) {
            return clone $definitions->getDefinition($this->containerName);
        }

        /** @var ContainerInterface $dataContainer */
        $dataContainer = (new \ReflectionClass($this->containerClassName))->newInstance($this->containerName);

        // Backwards compatibility.
        $this->getEventDispatcher()->dispatch(
            new BuildDataDefinitionEvent($dataContainer),
            BuildDataDefinitionEvent::NAME
        );

        $definitions->setDefinition($this->containerName, $dataContainer);

        return clone $dataContainer;
    }
}
