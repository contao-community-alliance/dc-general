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

namespace DcGeneral\Interfaces;

interface Environment
{
	/**
	 * Set the Controller for the current setup.
	 *
	 * @param \DcGeneral\Controller\Interfaces\Controller $objController The controller to use.
	 *
	 * @return Environment
	 */
	public function setController($objController);

	/**
	 * Retrieve the Controller from the current setup.
	 *
	 * @return \DcGeneral\Controller\Interfaces\Controller
	 */
	public function getController();

	/**
	 * Set the View for the current setup.
	 *
	 * @param \DcGeneral\View\Interfaces\View $objView The view to use.
	 *
	 * @return Environment
	 */
	public function setView($objView);

	/**
	 * Retrieve the Controller from the current setup.
	 *
	 * @return \DcGeneral\View\Interfaces\View
	 */
	public function getView();

	/**
	 * Retrieve the data definition
	 *
	 * @param \DcGeneral\DataDefinition\Interfaces\Container $objContainer
	 *
	 * @return Environment
	 */
	public function setDataDefinition($objContainer);

	/**
	 * @return \DcGeneral\DataDefinition\Interfaces\Container
	 */
	public function getDataDefinition();

	/**
	 * @param \DcGeneral\Interfaces\InputProvider $objInputProvider
	 *
	 * @return Environment
	 */
	public function setInputProvider($objInputProvider);

	/**
	 * @return \DcGeneral\Interfaces\InputProvider
	 */
	public function getInputProvider();

	/**
	 *
	 * @param \DcGeneral\Callbacks\CallbacksInterface $objCallbackHandler
	 *
	 * @return Environment
	 */
	public function setCallbackHandler($objCallbackHandler);

	/**
	 *
	 * @return \DcGeneral\Callbacks\CallbacksInterface
	 */
	public function getCallbackHandler();

	/**
	 * @param \DcGeneral\Panel\Interfaces\Container $objPanelContainer
	 *
	 * @return Environment
	 */
	public function setPanelContainer($objPanelContainer);

	/**
	 * @return \DcGeneral\Panel\Interfaces\Container
	 */
	public function getPanelContainer();

	/**
	 *
	 * @param \DcGeneral\Data\CollectionInterface $objCurrentCollection
	 *
	 * @return Environment
	 */
	public function setCurrentCollection($objCurrentCollection);

	/**
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getCurrentCollection();

	/**
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objCurrentModel
	 *
	 * @return Environment
	 */
	public function setCurrentModel($objCurrentModel);

	/**
	 *
	 * @return \DcGeneral\Data\Interfaces\Model
	 */
	public function getCurrentModel();

	/**
	 * Return the clipboard.
	 *
	 * @return \DcGeneral\Clipboard\Interfaces\Clipboard
	 */
	public function getClipboard();

	/**
	 * Set the the clipboard.
	 *
	 * @param \DcGeneral\Clipboard\Interfaces\Clipboard $objClipboard Clipboard instance.
	 *
	 * @return Environment
	 */
	public function setClipboard($objClipboard);
}
