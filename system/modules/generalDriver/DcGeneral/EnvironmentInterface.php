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

namespace DcGeneral;

interface EnvironmentInterface
{
	/**
	 * Set the Controller for the current setup.
	 *
	 * @param \DcGeneral\Controller\ControllerInterface $objController The controller to use.
	 *
	 * @return EnvironmentInterface
	 */
	public function setController($objController);

	/**
	 * Retrieve the Controller from the current setup.
	 *
	 * @return \DcGeneral\Controller\ControllerInterface
	 */
	public function getController();

	/**
	 * Set the View for the current setup.
	 *
	 * @param \DcGeneral\View\ViewInterface $objView The view to use.
	 *
	 * @return EnvironmentInterface
	 */
	public function setView($objView);

	/**
	 * Retrieve the Controller from the current setup.
	 *
	 * @return \DcGeneral\View\ViewInterface
	 */
	public function getView();

	/**
	 * Retrieve the data definition
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $objContainer
	 *
	 * @return EnvironmentInterface
	 */
	public function setDataDefinition($objContainer);

	/**
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	public function getDataDefinition();

	/**
	 * Retrieve the data definition of the parent table.
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $objContainer
	 *
	 * @return EnvironmentInterface
	 */
	public function setParentDataDefinition($objContainer);

	/**
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	public function getParentDataDefinition();

	/**
	 * @param \DcGeneral\InputProviderInterface $objInputProvider
	 *
	 * @return EnvironmentInterface
	 */
	public function setInputProvider($objInputProvider);

	/**
	 * @return \DcGeneral\InputProviderInterface
	 */
	public function getInputProvider();

	/**
	 *
	 * @param \DcGeneral\Callbacks\CallbacksInterface $objCallbackHandler
	 *
	 * @return EnvironmentInterface
	 *
	 * @deprecated Callback handlers are deprecated, use the Events instead.
	 */
	public function setCallbackHandler($objCallbackHandler);

	/**
	 *
	 * @return \DcGeneral\Callbacks\CallbacksInterface
	 *
	 * @deprecated Callback handlers are deprecated, use the Events instead.
	 */
	public function getCallbackHandler();

	/**
	 * Determine if the data provider with the given name exists.
	 *
	 * @param null $strSource
	 *
	 * @return mixed
	 */
	public function hasDataProvider($strSource = null);

	/**
	 * Retrieve the data provider for the named source.
	 *
	 * If a source name is given, the named driver will get returned, if not given, the default driver will get
	 * returned, The default is to be determined via: getEnvironment()->getDataDefinition()->getDataProvider()
	 *
	 * @param string|null $strSource The name of the source.
	 *
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($strSource = null);

	/**
	 * Register a data provider to the environment.
	 *
	 * @param string                          $strSource The name of the source.
	 *
	 * @param \DcGeneral\Data\DriverInterface $objDriver The driver instance to register under the given name.
	 *
	 * @return EnvironmentInterface
	 */
	public function addDataProvider($strSource, $objDriver);

	/**
	 * Remove a data provider from the environment.
	 *
	 * @param string $strSource The name of the source.
	 *
	 * @return mixed
	 */
	public function removeDataProvider($strSource);

	/**
	 * Return the clipboard.
	 *
	 * @return \DcGeneral\Clipboard\ClipboardInterface
	 */
	public function getClipboard();

	/**
	 * Set the the clipboard.
	 *
	 * @param \DcGeneral\Clipboard\ClipboardInterface $objClipboard Clipboard instance.
	 *
	 * @return EnvironmentInterface
	 */
	public function setClipboard($objClipboard);

	/**
	 * @param \DcGeneral\TranslatorInterface $manager
	 *
	 * @return \DcGeneral\EnvironmentInterface
	 */
	public function setTranslator(TranslatorInterface $manager);

	/**
	 * @return \DcGeneral\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * @param \DcGeneral\Event\EventPropagatorInterface $propagator
	 *
	 * @return \DcGeneral\EnvironmentInterface
	 */

	public function setEventPropagator($propagator);
	/**
	 * @return \DcGeneral\Event\EventPropagatorInterface
	 */
	public function getEventPropagator();
}
