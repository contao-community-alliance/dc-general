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

namespace DcGeneral\Controller;

use DcGeneral\DataContainerInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\EnvironmentInterface;

interface ControllerInterface
{
	/**
	 * Set the environment.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @return ControllerInterface
	 */
	public function setEnvironment(EnvironmentInterface $environment);

	/**
	 * Retrieve the attached environment.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * Retrieve the base config for retrieving data.
	 *
	 * This includes all auxiliary filters from DCA etc. but excludes the filters from panels.
	 *
	 * @return \DcGeneral\Data\ConfigInterface
	 */
	public function getBaseConfig();

	/**
	 * Scan for children of a given model.
	 *
	 * This method is ready for mixed hierarchy and will return all children and grandchildren for the given table
	 * (or originating table of the model, if no provider name has been given) for all levels and parent child conditions.
	 *
	 * @param ModelInterface $objModel        The model to assemble children from.
	 *
	 * @param string         $strDataProvider The name of the data provider to fetch children from.
	 *
	 * @return array
	 */
	public function assembleAllChildrenFrom($objModel, $strDataProvider = '');

	/**
	 * Update the current model from a post request. Additionally, trigger meta palettes, if installed.
	 *
	 * @param ModelInterface            $model
	 *
	 * @param \DcGeneral\Data\PropertyValueBagInterface $propertyValues
	 *
	 * @return ControllerInterface
	 */
	public function updateModelFromPropertyBag($model, $propertyValues);

	/**
	 * Return all supported languages from the default data driver.
	 *
	 * @param mixed $mixID
	 *
	 * @return array
	 */
	public function getSupportedLanguages($mixID);

	// TODO: we need to flesh this out some more out and add real interface methods. Currently this interface is rather useless.

	// FIXME: all methods below are to be removed or refined to be really only common sense methods like setting/getting parent etc.

	/**
	 * Set the DataContainerInterface.
	 *
	 * @param DataContainerInterface $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DataContainerInterface.
	 *
	 * @return DataContainerInterface
	 */
	public function getDC();

	public function generateAjaxPalette($strSelector);

	public function copy();

	public function create();

	public function cut();

	public function delete();

	public function edit();

	public function move();

	public function show();

	/**
	 * Show all entries from a table
	 *
	 * @return void | String if error
	 */
	public function showAll();

	public function executePostActions();
}
