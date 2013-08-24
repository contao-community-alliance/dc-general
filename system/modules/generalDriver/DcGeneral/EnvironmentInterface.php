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
	 * @param \DcGeneral\ViewInterface $objView The view to use.
	 *
	 * @return EnvironmentInterface
	 */
	public function setView($objView);

	/**
	 * Retrieve the Controller from the current setup.
	 *
	 * @return \DcGeneral\ViewInterface
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
	 */
	public function setCallbackHandler($objCallbackHandler);

	/**
	 *
	 * @return \DcGeneral\Callbacks\CallbacksInterface
	 */
	public function getCallbackHandler();

	/**
	 * @param \DcGeneral\Panel\PanelContainerInterface $objPanelContainer
	 *
	 * @return EnvironmentInterface
	 */
	public function setPanelContainer($objPanelContainer);

	/**
	 * @return \DcGeneral\Panel\PanelContainerInterface
	 */
	public function getPanelContainer();

	/**
	 *
	 * @param \DcGeneral\Data\CollectionInterface $objCurrentCollection
	 *
	 * @return EnvironmentInterface
	 */
	public function setCurrentCollection($objCurrentCollection);

	/**
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getCurrentCollection();

	/**
	 *
	 * @param ModelInterface $objCurrentModel
	 *
	 * @return EnvironmentInterface
	 */
	public function setCurrentModel($objCurrentModel);

	/**
	 *
	 * @return ModelInterface
	 */
	public function getCurrentModel();

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
}
