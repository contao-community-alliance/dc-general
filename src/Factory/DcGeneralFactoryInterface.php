<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This interface describes a DcGeneral factory.
 *
 * The factory is responsible for creating a DcGeneral instance including the data definition container and environment.
 */
interface DcGeneralFactoryInterface
{
    /**
     * Set the class name to use as environment.
     *
     * @param string $environmentClassName The class name.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setEnvironmentClassName($environmentClassName);

    /**
     * Retrieve the class name to use as environment.
     *
     * @return string
     */
    public function getEnvironmentClassName();

    /**
     * Set the name for the data definition container.
     *
     * @param string $containerName The class name.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setContainerName($containerName);

    /**
     * Retrieve the name for the data definition container.
     *
     * @return string
     */
    public function getContainerName();

    /**
     * Set the class name to use as data definition container.
     *
     * @param string $containerClassName The class name.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setContainerClassName($containerClassName);

    /**
     * Retrieve the class name to use as data definition container.
     *
     * @return string
     */
    public function getContainerClassName();

    /**
     * Set the class name to use as DcGeneral.
     *
     * @param string $dcGeneralClassName The class name.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setDcGeneralClassName($dcGeneralClassName);

    /**
     * Retrieve the class name to use as DcGeneral.
     *
     * @return string
     */
    public function getDcGeneralClassName();

    /**
     * Set the event dispatcher to use.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setEventDispatcher($dispatcher);

    /**
     * Get the event dispatcher to use.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * Set the translator to use.
     *
     * @param TranslatorInterface $translator The translator instance.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setTranslator(TranslatorInterface $translator);

    /**
     * Get the translator to use.
     *
     * @return TranslatorInterface
     */
    public function getTranslator();

    /**
     * Set the environment to use.
     *
     * @param EnvironmentInterface $environment The environment instance.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setEnvironment(EnvironmentInterface $environment = null);

    /**
     * Retrieve the environment to use.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment();

    /**
     * Set the data definition container to use.
     *
     * @param ContainerInterface $dataContainer The data definition container instance.
     *
     * @return DcGeneralFactoryInterface
     */
    public function setDataContainer(ContainerInterface $dataContainer = null);

    /**
     * Retrieve the data definition container.
     *
     * @return ContainerInterface
     */
    public function getDataContainer();

    /**
     * Create a new instance of DcGeneral.
     *
     * If no environment is given, a new one is created.
     *
     * @return DcGeneral
     */
    public function createDcGeneral();

    /**
     * Create a new instance of Environment.
     *
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
