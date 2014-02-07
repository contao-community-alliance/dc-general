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

use DcGeneral\Data\PropertyValueBagInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\EnvironmentInterface;

/**
 * This interface describes a controller.
 *
 * @package DcGeneral\Controller
 */
interface ControllerInterface
{
	/**
	 * Set the environment.
	 *
	 * @param EnvironmentInterface $environment The environment.
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
	 * @param ModelInterface            $model          The model to update.
	 *
	 * @param PropertyValueBagInterface $propertyValues The value bag to retrieve the values from.
	 *
	 * @return ControllerInterface
	 */
	public function updateModelFromPropertyBag($model, $propertyValues);

	/**
	 * Return all supported languages from the default data driver.
	 *
	 * @param mixed $mixID The id of a model for which the languages shall be retrieved.
	 *
	 * @return array
	 */
	public function getSupportedLanguages($mixID);
}
