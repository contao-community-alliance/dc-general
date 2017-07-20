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

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface EnvironmentInterface.
 *
 * This interface describes the environment of a DcGeneral instance. It holds reference to the data providers, the view,
 * the data definition etc.
 * One could say the Environment is the glue of DcGeneral, holding everything together.
 */
interface EnvironmentInterface
{
    /**
     * Set the Controller for the current setup.
     *
     * @param ControllerInterface $objController The controller to use.
     *
     * @return EnvironmentInterface
     */
    public function setController($objController);

    /**
     * Retrieve the Controller from the current setup.
     *
     * @return ControllerInterface
     */
    public function getController();

    /**
     * Set the View for the current setup.
     *
     * @param ViewInterface $objView The view to use.
     *
     * @return EnvironmentInterface
     */
    public function setView($objView);

    /**
     * Retrieve the View from the current setup.
     *
     * @return ViewInterface
     */
    public function getView();

    /**
     * Set the data definition for this instance.
     *
     * @param ContainerInterface $objContainer The data definition container to store.
     *
     * @return EnvironmentInterface
     */
    public function setDataDefinition($objContainer);

    /**
     * Retrieve the data definition for this instance.
     *
     * @return ContainerInterface
     */
    public function getDataDefinition();

    /**
     * Set the data definition of the parent container.
     *
     * @param ContainerInterface $objContainer The data definition container to store.
     *
     * @return EnvironmentInterface
     */
    public function setParentDataDefinition($objContainer);

    /**
     * Retrieve the data definition for the parent container. This applies only when in parented mode.
     *
     * @return ContainerInterface
     */
    public function getParentDataDefinition();

    /**
     * Set the data definition of the root container.
     *
     * @param ContainerInterface $objContainer The data definition container to store.
     *
     * @return EnvironmentInterface
     */
    public function setRootDataDefinition($objContainer);

    /**
     * Retrieve the data definition for the root container. This applies only when in hierarchical mode.
     *
     * @return ContainerInterface
     */
    public function getRootDataDefinition();

    /**
     * Set the session storage to use.
     *
     * @param SessionStorageInterface $sessionStorage The session storage to use.
     *
     * @return EnvironmentInterface
     */
    public function setSessionStorage(SessionStorageInterface $sessionStorage);

    /**
     * Retrieve the session storage.
     *
     * @return SessionStorageInterface
     */
    public function getSessionStorage();

    /**
     * Set the input provider to use.
     *
     * @param InputProviderInterface $objInputProvider The input provider to use.
     *
     * @return EnvironmentInterface
     */
    public function setInputProvider($objInputProvider);

    /**
     * Retrieve the input provider.
     *
     * @return InputProviderInterface
     */
    public function getInputProvider();

    /**
     * Set the base config registry to use.
     *
     * @param BaseConfigRegistryInterface $baseConfigRegistry The input provider to use.
     *
     * @return EnvironmentInterface
     */
    public function setBaseConfigRegistry($baseConfigRegistry);

    /**
     * Retrieve the base config registry.
     *
     * @return BaseConfigRegistryInterface
     */
    public function getBaseConfigRegistry();

    /**
     * Determine if the data provider with the given name exists.
     *
     * @param string|null $strSource The source name to check the providers for.
     *
     * @return mixed
     */
    public function hasDataProvider($strSource = null);

    /**
     * Retrieve the data provider for the named source.
     *
     * If a source name is given, the named data provider will get returned, if not given, the default data provider
     * will get returned, the default is to be determined via: getEnvironment()->getDataDefinition()->getDataProvider()
     *
     * @param string|null $strSource The name of the source.
     *
     * @return DataProviderInterface
     */
    public function getDataProvider($strSource = null);

    /**
     * Register a data provider to the environment.
     *
     * @param string                $strSource    The name of the source.
     *
     * @param DataProviderInterface $dataProvider The data provider instance to register under the given name.
     *
     * @return EnvironmentInterface
     */
    public function addDataProvider($strSource, $dataProvider);

    /**
     * Remove a data provider from the environment.
     *
     * @param string $strSource The name of the source.
     *
     * @return EnvironmentInterface
     */
    public function removeDataProvider($strSource);

    /**
     * Return the clipboard.
     *
     * @return ClipboardInterface
     */
    public function getClipboard();

    /**
     * Set the the clipboard.
     *
     * @param ClipboardInterface $objClipboard Clipboard instance.
     *
     * @return EnvironmentInterface
     */
    public function setClipboard($objClipboard);

    /**
     * Set the translation manager to use.
     *
     * @param TranslatorInterface $manager The translation manager.
     *
     * @return EnvironmentInterface
     */
    public function setTranslator(TranslatorInterface $manager);

    /**
     * Retrieve the translation manager to use.
     *
     * @return TranslatorInterface
     */
    public function getTranslator();

    /**
     * Set the event dispatcher to use.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return EnvironmentInterface
     */
    public function setEventDispatcher($dispatcher);

    /**
     * Get the event dispatcher to use.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}
