<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

interface InterfaceGeneralModel extends IteratorAggregate
{

	/**
	 * Copy this model, without the id.
	 *
	 * @return InterfaceGeneralModel
	 */
	public function __clone();

	/**
	 * Get the id for this modell.
	 *
	 * @return string The ID for this modell.
	 */
	public function getID();

	/**
	 * Fetch property from model.
	 */
	public function getProperty($strPropertyName);

	/**
	 * Fetch all properties from model.
	 *
	 * return array
	 */
	public function getPropertiesAsArray();

	/**
	 * Fetch meta information from model.
	 *
	 * @param string $strMetaName the meta information to retrieve.
	 *
	 * @return mixed|null the set meta information or null if undefined.
	 */
	public function getMeta($strMetaName);

	/**
	 * Set the id for this object
	 *
	 * @param mixed $mixID Could be a integer, string or anything else
	 */
	public function setID($mixID);

	/**
	 * Update property in model.
	 */
	public function setProperty($strPropertyName, $varValue);

	/**
	 * Update all properties in model.
	 *
	 * @param array $arrProperties
	 */
	public function setPropertiesAsArray($arrProperties);

	/**
	 * Update meta information in model.
	 *
	 * @param string $strMetaName the meta information name.
	 *
	 * @param mixed $varValue the meta information to store.
	 *
	 * @return void
	 */
	public function setMeta($strMetaName, $varValue);

	/**
	 * Check if this model have any properties.
	 *
	 * @return boolean True|False
	 */
	public function hasProperties();

	/**
	 * Return the data provider name.
	 *
	 * @return string the name of the corresponding data provider.
	 */
	public function getProviderName();

}