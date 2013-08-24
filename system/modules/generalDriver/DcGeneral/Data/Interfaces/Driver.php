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

namespace DcGeneral\Data\Interfaces;

use DcGeneral\Data\CollectionInterface;

interface Driver
{
	/**
	 * Set base config with source and other necessary parameter.
	 *
	 * @param array $arrConfig The configuration to use.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when no source has been defined.
	 */
	public function setBaseConfig(array $arrConfig);

	/**
	 * Return an empty configuration object.
	 *
	 * @return ConfigInterface
	 */
	public function getEmptyConfig();

	/**
	 * Fetch an empty single record (new model).
	 *
	 * @return Model
	 */
	public function getEmptyModel();

	/**
	 * Fetch an empty single collection (new model list).
	 *
	 * @return CollectionInterface
	 */
	public function getEmptyCollection();

	/**
	 * Fetch a single or first record by id or filter.
	 *
	 * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
	 *
	 * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
	 *
	 * @param ConfigInterface $objConfig
	 *
	 * @return Model
	 */
	public function fetch(ConfigInterface $objConfig);

	/**
	 * Fetch all records (optional filtered, sorted and limited).
	 *
	 * This returns a collection of all models matching the config object. If idOnly is true, an array containing all
	 * matching ids is returned.
	 *
	 * @param ConfigInterface $objConfig
	 *
	 * @return CollectionInterface|array
	 */
	public function fetchAll(ConfigInterface $objConfig);

	/**
	 * Retrieve all unique values for the given property.
	 *
	 * The result set will be an array containing all unique values contained in the data provider.
	 * Note: this only re-ensembles really used values for at least one data set.
	 *
	 * The only information being interpreted from the passed config object is the first property to fetch and the
	 * filter definition.
	 *
	 * @param ConfigInterface $objConfig   The filter config options.
	 *
	 * @return CollectionInterface
	 */
	public function getFilterOptions(ConfigInterface $objConfig);

	/**
	 * Return the amount of total items (filtering may be used in the config).
	 *
	 * @param ConfigInterface $objConfig
	 *
	 * @return int
	 */
	public function getCount(ConfigInterface $objConfig);

	/**
	 * Save an item to the data provider.
	 *
	 * If the item does not have an Id yet, the save operation will add it as a new row to the database and
	 * populate the Id of the model accordingly.
	 *
	 * @param Model $objItem   The model to save back.
	 *
	 * @return Model The passed model.
	 */
	 public function save(Model $objItem);

	/**
	 * Save a collection of items to the data provider.
	 *
	 * @param CollectionInterface $objItems The collection containing all items to be saved.
	 *
	 * @return void
	 */
	public function saveEach(CollectionInterface $objItems);

	/**
	 * Delete an item.
	 *
	 * The given value may be either integer, string or an instance of Model
	 *
	 * @param mixed $item Id or the model itself, to delete.
	 *
	 * @throws \RuntimeException when an unusable object has been passed.
	 */
	public function delete($item);

	/**
	 * Save a new version of a model.
	 *
	 * @param Model $objModel    The model for which a new version shall be created.
	 *
	 * @param string                $strUsername The username to attach to the version as creator.
	 *
	 * @return void
	 */
	public function saveVersion(Model $objModel, $strUsername);

	/**
	 * Return a model based of the version information.
	 *
	 * @param mixed $mixID      The ID of the record.
	 *
	 * @param mixed $mixVersion The ID of the version.
	 *
	 * @return Model
	 */
	public function getVersion($mixID, $mixVersion);

	/**
	 * Return a list with all versions for the model with the given Id.
	 *
	 * @param mixed   $mixID         The ID of the row.
	 *
	 * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
	 *                               returned.
	 *
	 * @return CollectionInterface
	 */
	public function getVersions($mixID, $blnOnlyActive = false);

	/**
	 * Set a version as active.
	 *
	 * @param mixed $mixID      The ID of the model.
	 *
	 * @param mixed $mixVersion The version number to set active.
	 */
	public function setVersionActive($mixID, $mixVersion);

	/**
	 * Retrieve the current active version for a model.
	 *
	 * @param mixed $mixID The ID of the model.
	 *
	 * @return mixed The current version number of the requested row.
	 */
	public function getActiveVersion($mixID);

	/**
	 * Reset the fallback field.
	 *
	 * This clears the given property in all items in the data provider to an empty value.
	 *
	 * Documentation:
	 *      Evaluation - fallback => If true the field can only be assigned once per table.
	 *
	 * @param string $strField The field to reset.
	 *
	 * @return void
	 */
	public function resetFallback($strField);

	/**
	 * Check if the value is unique in the data provider.
	 *
	 * @param string $strField the field in which to test.
	 *
	 * @param mixed  $varNew   the value about to be saved.
	 *
	 * @param int    $intId    the (optional) id of the item currently in scope - pass null for new items.
	 *
	 * Documentation:
	 *      Evaluation - unique => If true the field value cannot be saved if it exists already.
	 *
	 * @return boolean
	 */
	public function isUniqueValue($strField, $varNew, $intId = null);

	/**
	 * Check if a property with the given name exists in the data provider.
	 *
	 * @param string $strField The name of the property to search.
	 *
	 * @return boolean
	 */
	public function fieldExists($strField);

	/**
	 * Check if two models have the same values in all properties.
	 *
	 * @param Model $objModel1 The first model to compare.
	 *
	 * @param Model $objModel2 The second model to compare.
	 *
	 * @return boolean True - If both models are same, false if not.
	 */
	public function sameModels($objModel1 , $objModel2);
}
