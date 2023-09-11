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

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default implementation of an environment.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DefaultEnvironment implements EnvironmentInterface
{
    /**
     * The controller.
     *
     * @var ControllerInterface|null
     */
    protected $objController = null;

    /**
     * The view in use.
     *
     * @var ViewInterface|null
     */
    protected $objView = null;

    /**
     * The data container definition.
     *
     * @var ContainerInterface|null
     */
    protected $objDataDefinition = null;

    /**
     * The data container definition of the parent table.
     *
     * @var ContainerInterface|null
     */
    protected $objParentDataDefinition = null;

    /**
     * The data container definition of the root table.
     *
     * @var ContainerInterface|null
     */
    protected $objRootDataDefinition = null;

    /**
     * The session storage.
     *
     * @var SessionStorageInterface|null
     */
    protected $sessionStorage = null;

    /**
     * The attached input provider.
     *
     * @var InputProviderInterface|null
     */
    protected $objInputProvider = null;

    /**
     * The attached base config registry.
     *
     * @var BaseConfigRegistryInterface|null
     */
    protected $baseConfigRegistry = null;

    /**
     * The registered data providers.
     *
     * @var array<string, DataProviderInterface>
     */
    protected $arrDataProvider = [];

    /**
     * The clipboard in use.
     *
     * @var ClipboardInterface|null
     */
    protected $objClipboard = null;

    /**
     * The translator in use.
     *
     * @var TranslatorInterface|null
     */
    protected $translator = null;

    /**
     * The event propagator in use.
     *
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher = null;

    /**
     * {@inheritdoc}
     */
    public function setController($controller)
    {
        $this->objController = $controller;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->objController;
    }

    /**
     * {@inheritdoc}
     */
    public function setView($view)
    {
        $this->objView = $view;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        return $this->objView;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataDefinition($dataDefinition)
    {
        $this->objDataDefinition = $dataDefinition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataDefinition()
    {
        return $this->objDataDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentDataDefinition($objParentDataDefinition)
    {
        $this->objParentDataDefinition = $objParentDataDefinition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentDataDefinition()
    {
        return $this->objParentDataDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function setRootDataDefinition($rootDataDefinition)
    {
        $this->objRootDataDefinition = $rootDataDefinition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDataDefinition()
    {
        return $this->objRootDataDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function setSessionStorage(SessionStorageInterface $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionStorage()
    {
        return $this->sessionStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function setInputProvider($inputProvider)
    {
        $this->objInputProvider = $inputProvider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputProvider()
    {
        return $this->objInputProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseConfigRegistry($baseConfigRegistry)
    {
        $this->baseConfigRegistry = $baseConfigRegistry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseConfigRegistry()
    {
        return $this->baseConfigRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDataProvider($source = null)
    {
        if (null === $source) {
            $definition = $this->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $source = $definition->getBasicDefinition()->getDataProvider();
            assert(\is_string($source));
        }

        return isset($this->arrDataProvider[$source]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When an undefined provider is requested.
     */
    public function getDataProvider($strSource = null)
    {
        if (null === $strSource) {
            $definition = $this->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $strSource = $definition->getBasicDefinition()->getDataProvider();
            assert(\is_string($strSource));
        }

        if (isset($this->arrDataProvider[$strSource])) {
            return $this->arrDataProvider[$strSource];
        }

        throw new DcGeneralRuntimeException(\sprintf('Data provider %s not defined', $strSource));
    }

    /**
     * {@inheritdoc}
     */
    public function addDataProvider($strSource, $dataProvider)
    {
        // Force removal of an potentially registered data provider to ease sub-classing.
        $this->removeDataProvider($strSource);

        $this->arrDataProvider[$strSource] = $dataProvider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDataProvider($strSource)
    {
        if (isset($this->arrDataProvider[$strSource])) {
            unset($this->arrDataProvider[$strSource]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClipboard()
    {
        return $this->objClipboard;
    }

    /**
     * {@inheritdoc}
     */
    public function setClipboard($objClipboard)
    {
        $this->objClipboard = $objClipboard;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslator(TranslatorInterface $manager)
    {
        $this->translator = $manager;

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
}
