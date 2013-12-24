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

/**
 * Interface EnvironmentInterface.
 *
 * This interface describes the environment of a DcGeneral instance. It holds reference to the data providers, the view,
 * the data definition etc.
 * One could say the Environment is the glue of DcGeneral, holding everything together.
 *
 * @package DcGeneral
 */
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
	 * Retrieve the View from the current setup.
	 *
	 * @return \DcGeneral\View\ViewInterface
	 */
	public function getView();

	/**
	 * Set the data definition for this instance.
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $objContainer The data definition container to store.
	 *
	 * @return EnvironmentInterface
	 */
	public function setDataDefinition($objContainer);

	/**
	 * Retrieve the data definition for this instance.
	 *
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	public function getDataDefinition();

	/**
	 * Set the data definition of the parent container.
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $objContainer The data definition container to store.
	 *
	 * @return EnvironmentInterface
	 */
	public function setParentDataDefinition($objContainer);

	/**
	 * Retrieve the data definition for the parent container. This applies only when in parented mode.
	 *
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	public function getParentDataDefinition();

	/**
	 * Set the data definition of the root container.
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $objContainer The data definition container to store.
	 *
	 * @return EnvironmentInterface
	 */
	public function setRootDataDefinition($objContainer);

	/**
	 * Retrieve the data definition for the root container. This applies only when in hierarchical mode.
	 *
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	public function getRootDataDefinition();

	/**
	 * Set the input provider to use.
	 *
	 * @param \DcGeneral\InputProviderInterface $objInputProvider The input provider to use.
	 *
	 * @return EnvironmentInterface
	 */
	public function setInputProvider($objInputProvider);

	/**
	 * Retrieve the input provider.
	 *
	 * @return \DcGeneral\InputProviderInterface
	 */
	public function getInputProvider();

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
	 * @return EnvironmentInterface
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
	 * Set the translation manager to use.
	 *
	 * @param TranslatorInterface $manager The translation manager.
	 *
	 * @return \DcGeneral\EnvironmentInterface
	 */
	public function setTranslator(TranslatorInterface $manager);

	/**
	 * Retrieve the translation manager to use.
	 *
	 * @return \DcGeneral\TranslatorInterface
	 */
	public function getTranslator();

	/**
	 * Set the event propagator to use.
	 *
	 * @param \DcGeneral\Event\EventPropagatorInterface $propagator The event propagator to use.
	 *
	 * @return \DcGeneral\EnvironmentInterface
	 */

	public function setEventPropagator($propagator);

	/**
	 * Retrieve the event propagator to use.
	 *
	 * @return \DcGeneral\Event\EventPropagatorInterface
	 */
	public function getEventPropagator();
}
