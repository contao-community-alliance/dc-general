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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Simon Kusterer <simon@soped.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DC;

use Contao\DataContainer;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * This class is only present so Contao can instantiate a backend properly as it needs a \DataContainer descendant.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends DataContainer implements DataContainerInterface
{
    /**
     * The environment attached to this DC.
     *
     * @var EnvironmentInterface|null
     */
    protected $objEnvironment;

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new instance.
     *
     * @param string              $tableName The table name.
     * @param array               $module    The modules.
     * @param CacheInterface|null $cache     The cache.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($tableName, array $module = [], CacheInterface $cache = null)
    {
        // Prevent "Recoverable error: Argument X passed to SomClass::someMethod() must be an instance of DataContainer,
        // instance of ContaoCommunityAlliance\DcGeneral\DC_General given" in callbacks.
        if (!\class_exists('\DataContainer', false)) {
            \class_alias('\Contao\DataContainer', '\DataContainer');
        }

        $tableNameCallback = $this->getTablenameCallback($tableName);

        $dispatcher = $this->getEventDispatcher();
        $fetcher    = \Closure::bind(function (PopulateEnvironmentEvent $event) use ($tableNameCallback) {
            $definition = $event->getEnvironment()->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            // We need to capture the correct environment and save it for later use.
            if ($tableNameCallback !== $definition->getName()) {
                return;
            }
            $this->objEnvironment = $event->getEnvironment();
        }, $this, $this);
        assert($fetcher instanceof \Closure);
        $dispatcher->addListener(PopulateEnvironmentEvent::NAME, $fetcher, 4800);

        (new DcGeneralFactory($cache))
            ->setContainerName($tableNameCallback)
            ->setEventDispatcher($dispatcher)
            ->setTranslator($this->getTranslator())
            ->createDcGeneral();
        $dispatcher->removeListener(PopulateEnvironmentEvent::NAME, $fetcher);

        $clipboard = $this->getEnvironment()->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        // Load the clipboard.
        $clipboard->loadFrom($this->getEnvironment());

        // Execute AJAX request, called from Backend::getBackendModule
        // we have to do this here, as otherwise the script will exit as it only checks for DC_Table and DC_File
        // derived classes.
        $this->checkAjaxCall();
    }

    /**
     * Retrieve the event dispatcher from the DIC.
     *
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        return $dispatcher;
    }

    /**
     * Get the translator from the service container.
     *
     * @return TranslatorInterface
     */
    private function getTranslator(): TranslatorInterface
    {
        $translator = System::getContainer()->get('cca.translator.contao_translator');
        assert($translator instanceof TranslatorInterface);

        return $translator;
    }

    /**
     * Check if we have an ajax call currently and if so, execute the action accordingly.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function checkAjaxCall()
    {
        if (
            !empty($_POST)
            && (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            $this->getViewHandler()->handleAjaxCall();
        }
    }

    /**
     * Call the table name callback.
     *
     * @param string $tableName The current table name.
     *
     * @return string New name of current table.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTablenameCallback($tableName)
    {
        if (
            isset($GLOBALS['TL_DCA'][$tableName]['config']['tablename_callback'])
            && \is_array($GLOBALS['TL_DCA'][$tableName]['config']['tablename_callback'])
        ) {
            foreach ($GLOBALS['TL_DCA'][$tableName]['config']['tablename_callback'] as $callback) {
                $tableName = Callbacks::call($callback, $tableName, $this) ?: $tableName;
            }
        }

        return $tableName;
    }

    /**
     * Magic getter.
     *
     * @param string $name Name of the property to retrieve.
     *
     * @return mixed
     *
     * @throws DcGeneralRuntimeException If an invalid key is requested.
     *
     * @deprecated magic access is deprecated.
     */
    public function __get($name)
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        switch ($name) {
            case 'id':
                // Step 1: Find the parent id for the Contao breadcrumb.
                $idParameter = $inputProvider->hasParameter('id') ? 'id' : 'pid';

                // Step 2: Check if the parameter really exists.
                if (false === $inputProvider->hasParameter($idParameter)) {
                    break;
                }

                return ModelId::fromSerialized($inputProvider->getParameter($idParameter))->getId();
            case 'table':
                $definition = $environment->getDataDefinition();
                assert($definition instanceof ContainerInterface);

                return $definition->getName();
            default:
        }

        throw new DcGeneralRuntimeException('Unsupported getter function for \'' . $name . '\' in DC_General.');
    }

    /**
     * Retrieve the name of the data container.
     *
     * @return string
     */
    public function getName()
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        return $definition->getName();
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     *
     * @throws DcGeneralRuntimeException When no environment has been set.
     */
    public function getEnvironment()
    {
        if (null === $this->objEnvironment) {
            throw new DcGeneralRuntimeException('No Environment set.');
        }

        return $this->objEnvironment;
    }

    /**
     * Retrieve the view.
     *
     * @return ViewInterface
     */
    public function getViewHandler()
    {
        $view = $this->getEnvironment()->getView();
        assert($view instanceof ViewInterface);

        return $view;
    }

    /**
     * Retrieve the controller.
     *
     * @return ControllerInterface
     */
    public function getControllerHandler()
    {
        $controller = $this->getEnvironment()->getController();
        assert($controller instanceof ControllerInterface);

        return $controller;
    }

    /**
     * Delegate all calls directly to current view.
     *
     * @param string $name      Name of the method.
     * @param array  $arguments Array of arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $controller = $this->getEnvironment()->getController();
        assert($controller instanceof ControllerInterface);

        return $controller->handle(new Action($name, $arguments));
    }

    /**
     * Call the desired user action with an implicit fallback to the "showAll" action when none has been requested.
     *
     * @return string
     */
    protected function callAction()
    {
        $environment = $this->getEnvironment();

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);

        $action = new Action($inputProvider->getParameter('act') ?: 'showAll');

        return $controller->handle($action);
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function copy()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function create()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function cut()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function delete()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function edit()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function move()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function show()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function showAll()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function undo()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \DataContainer
     *
     * @return never
     *
     * @throws DcGeneralRuntimeException Throws exception because method is not supported.
     */
    public function getPalette()
    {
        throw new DcGeneralRuntimeException('DC General does not support $dc->getPalette().');
    }

    /**
     * Do not use.
     *
     * @param mixed $varValue Ignored.
     *
     * @deprecated Only here as requirement of \DataContainer
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Throws exception because method is not supported.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function save($varValue)
    {
        throw new DcGeneralRuntimeException('DC General does not support $dc->save.');
    }
}
