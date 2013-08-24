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

namespace DcGeneral\Data;

interface ModelInterface extends \IteratorAggregate
{
	/**
	 * Copy this model, without the id.
	 *
	 * @return ModelInterface
	 */
	public function __clone();

	/**
	 * Get the id for this model.
	 *
	 * @return mixed The Id for this model.
	 */
	public function getId();

	/**
	 * Fetch the property with the given name from the model.
	 *
	 * This method returns null if an unknown property is retrieved.
	 *
	 * @param string $strPropertyName The property name to be retrieved.
	 *
	 * @return mixed The value of the given property.
	 */
	public function getProperty($strPropertyName);

	/**
	 * Fetch all properties from the model as an name => value array.
	 *
	 * @return array
	 */
	public function getPropertiesAsArray();

	/**
	 * Fetch meta information from model.
	 *
	 * @param string $strMetaName The meta information to retrieve.
	 *
	 * @return mixed The set meta information or null if undefined.
	 */
	public function getMeta($strMetaName);

	/**
	 * Set the id for this object.
	 *
	 * NOTE: when the Id has been set once to a non null value, it can NOT be changed anymore.
	 *
	 * Normally this should only be called from inside of the implementing provider.
	 *
	 * @param mixed $mixId Could be a integer, string or anything else - depends on the provider implementation.
	 *
	 * @return void
	 */
	public function setId($mixId);

	/**
	 * Update the property value in the model.
	 *
	 * @param string $strPropertyName
	 *
	 * @param mixed  $varValue
	 *
	 * @return void
	 */
	public function setProperty($strPropertyName, $varValue);

	/**
	 * Update all properties in the model.
	 *
	 * @param array $arrProperties The property values as name => value pairs.
	 *
	 * @return void
	 */
	public function setPropertiesAsArray($arrProperties);

	/**
	 * Update meta information in the model.
	 *
	 * @param string $strMetaName The meta information name.
	 *
	 * @param mixed  $varValue    The meta information value to store.
	 *
	 * @return void
	 */
	public function setMeta($strMetaName, $varValue);

	/**
	 * Check if this model have any properties.
	 *
	 * @return boolean true if any property has been stored, false otherwise.
	 */
	public function hasProperties();

	/**
	 * Return the data provider name.
	 *
	 * @return string the name of the corresponding data provider.
	 */
	public function getProviderName();
}
