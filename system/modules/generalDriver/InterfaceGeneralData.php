<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

interface InterfaceGeneralData
{

	/**
	 * Set base config with source and other neccesary prameter
	 *
	 * @param array $arrConfig
	 * @throws Excpetion
	 */
	public function setBaseConfig(array $arrConfig);

	/**
	 * Return empty config object
	 *
	 * @return InterfaceGeneralDataConfig
	 */
	public function getEmptyConfig();

	/**
	 * Fetch an empty single record (new item).
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getEmptyModel();

	/**
	 * Fetch an empty new collection.
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getEmptyCollection();

	/**
	 * Fetch a single record by id.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralModel
	 */
	public function fetch(GeneralDataConfigDefault $objConfig);

	/**
	 * Fetch all records (optional limited).
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function fetchAll(GeneralDataConfigDefault $objConfig);

	/**
	 * Return the amount of total items.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return int
	 */
	public function getCount(GeneralDataConfigDefault $objConfig);

	/**
	 * save back an item
	 *
	 * @param InterfaceGeneralModel $objItem
	 * @param bool $recursive
	 * Save child records, for each property a child provider is registered.
	 */
	 public function save(InterfaceGeneralModel $objItem, $recursive = false);

	/**
	 * Save a collection of items.
	 *
	 * @param InterfaceGeneralCollection $items a list with all items
	 */
	public function saveEach(InterfaceGeneralCollection $items, $recursive = false);

	/**
	 * Delete an item.
	 *
	 * @param int|Module Id or the object itself, to delete
	 */
	public function delete($item);

	/**
	 * Save a new Version of a record
	 *
	 * @param int $intID ID of current record
	 * @param string $strVersion Version number
	 * @return void
	 */
	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername);

	/**
	 * Return a model based of the version information
	 *
	 * @param mix $mixID The ID of record
	 * @param mix $mixVersion The ID of the Version
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getVersion($mixID, $mixVersion);

	/**
	 * Return a list with all versions for this row
	 *
	 * @param mixed $mixID The ID of record
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getVersions($mixID);

	/**
	 * Set a Version as active.
	 *
	 * @param mix $mixID The ID of record
	 * @param mix $mixVersion The ID of the Version
	 */
	public function setVersionActive($mixID, $mixVersion);

	/**
	 * Return the active version from a record
	 *
	 * @param mix $mixID The ID of record
	 *
	 * @return mix Version ID
	 */
	public function getActiveVersion($mixID);

	/**
	 * Reste the fallback field
	 *
	 * Documentation:
	 *      Evaluation - fallback => If true the field can only be assigned once per table.
	 *
	 * @return void
	 */
	public function resetFallback($strField);

	/**
	 * Check if the value is unique in table
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
	 * Check if the value exists in the table
	 *
	 * @return boolean
	 */
	public function fieldExists($strField);

	/**
	 * Check if two models have the same properties
	 *
	 * @param InterfaceGeneralModel $objModel1
	 * @param InterfaceGeneralModel $objModel2
	 *
	 * return boolean True - If both models are same, false if not
	 */
	public function sameModels($objModel1 , $objModel2);

}