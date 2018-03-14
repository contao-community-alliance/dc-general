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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Simon Kusterer <simon@soped.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DC;

use Contao\DataContainer;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class is only present so Contao can instantiate a backend properly as it needs a \DataContainer descendant.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class General extends DataContainer implements DataContainerInterface
{
    /**
     * The environment attached to this DC.
     *
     * @var EnvironmentInterface
     */
    protected $objEnvironment;

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Create a new instance.
     *
     * @param string $strTable The table name.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($strTable)
    {
        // Prevent "Recoverable error: Argument X passed to SomClass::someMethod() must be an instance of DataContainer,
        // instance of ContaoCommunityAlliance\DcGeneral\DC_General given" in callbacks.
        if (!\class_exists('\DataContainer', false)) {
            \class_alias('\Contao\DataContainer', '\DataContainer');
        }
        $strTable   = $this->getTablenameCallback($strTable);
        $translator = $this->getTranslator();

        $dispatcher = $this->getEventDispatcher();
        $fetcher    = \Closure::bind(function (PopulateEnvironmentEvent $event) use ($strTable) {
            // We need to capture the correct environment and save it for later use.
            if ($strTable !== $event->getEnvironment()->getDataDefinition()->getName()) {
                return;
            }
            $this->objEnvironment = $event->getEnvironment();
        }, $this, $this);
        $dispatcher->addListener(PopulateEnvironmentEvent::NAME, $fetcher, 4800);

        $factory = new DcGeneralFactory();

        $factory
            ->setContainerName($strTable)
            ->setEventDispatcher($dispatcher)
            ->setTranslator($translator)
            ->createDcGeneral();
        $dispatcher->removeListener(PopulateEnvironmentEvent::NAME, $fetcher);

        // Load the clipboard.
        $this
            ->getEnvironment()
            ->getClipboard()
            ->loadFrom($this->getEnvironment());

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
    private function getEventDispatcher()
    {
        return System::getContainer()->get('event_dispatcher');
    }

    /**
     * Get the translator from the service container.
     *
     * @return TranslatorInterface
     */
    private function getTranslator()
    {
        return System::getContainer()->get('cca.translator.contao_translator');
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
        if (!empty($_POST)
            && (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
        ) {
            $this->getViewHandler()->handleAjaxCall();
        }
    }

    /**
     * Call the table name callback.
     *
     * @param string $strTable The current table name.
     *
     * @return string New name of current table.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTablenameCallback($strTable)
    {
        if (isset($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
            && \is_array($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
        ) {
            foreach ($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'] as $callback) {
                $strCurrentTable = Callbacks::call($callback, $strTable, $this);

                if ($strCurrentTable != null) {
                    $strTable = $strCurrentTable;
                }
            }
        }

        return $strTable;
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
        $environment    = $this->getEnvironment();
        $inputProvider  = $environment->getInputProvider();
        $dataDefinition = $environment->getDataDefinition();

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
                return $dataDefinition->getName();
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
        return $this->getEnvironment()->getDataDefinition()->getName();
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
        if (!$this->objEnvironment) {
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
        return $this->getEnvironment()->getView();
    }

    /**
     * Retrieve the controller.
     *
     * @return ControllerInterface
     */
    public function getControllerHandler()
    {
        return $this->getEnvironment()->getController();
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
        return $this->getEnvironment()->getController()->handle(new Action($name, $arguments));
    }

    /**
     * Call the desired user action with an implicit fallback to the "showAll" action when none has been requested.
     *
     * @return string
     */
    protected function callAction()
    {
        $environment = $this->getEnvironment();
        $act         = $environment->getInputProvider()->getParameter('act');
        $action      = new Action($act ?: 'showAll');
        return $environment->getController()->handle($action);
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
     * @return void
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
