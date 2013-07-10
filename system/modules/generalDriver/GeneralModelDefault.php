<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralModelDefault extends AbstractGeneralModel
{

	/**
	 * A list with all properties.
	 *
	 * @var array
	 */
	protected $arrProperties = array();

	/**
	 * The Id of this model.
	 *
	 * @var mixed
	 */
	protected $mixID = null;

	/**
	 * The name of the corresponding data provider.
	 *
	 * @var string
	 */
	protected $strProviderName = null;

	/**
	 * Copy this model, without the id.
	 *
	 * @return InterfaceGeneralModel
	 */
	public function __clone()
	{
		$this->mixID = null;
	}

	/**
	 * Get the id for this model.
	 *
	 * @return string The ID for this model.
	 */
	public function getID()
	{
		return $this->mixID;
	}

	/**
	 * Fetch the property with the given name from the model.
	 *
	 * This method returns null if an unknown property is retrieved.
	 *
	 * @param string $strPropertyName The property name to be retrieved.
	 *
	 * @return mixed The value of the given property.
	 */
	public function getProperty($strPropertyName)
	{
		if ($strPropertyName == 'id')
		{
			return $this->getID();
		}

		if (array_key_exists($strPropertyName, $this->arrProperties))
		{
			return $this->arrProperties[$strPropertyName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Fetch all properties from the model as an name => value array.
	 *
	 * @return array
	 */
	public function getPropertiesAsArray()
	{
		$arrArray       = $this->arrProperties;
		$arrArray["id"] = $this->mixID;

		return $arrArray;
	}

	/**
	 * Set the id for this object.
	 *
	 * NOTE: when the Id has been set once to a non null value, it can NOT be changed anymore.
	 *
	 * Normally this should only be called from inside of the implementing provider.
	 *
	 * @param mixed $mixID Could be a integer, string or anything else - depends on the provider implementation.
	 *
	 * @return void
	 */
	public function setID($mixID)
	{
		if ($this->mixID == null)
		{
			$this->mixID = $mixID;
		}
	}

	/**
	 * Update the property value in the model.
	 *
	 * @param string $strPropertyName
	 *
	 * @param mixed  $varValue
	 *
	 * @return void
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		$this->arrProperties[$strPropertyName] = $varValue;
	}

	/**
	 * Update all properties in the model.
	 *
	 * @param array $arrProperties The property values as name => value pairs.
	 *
	 * @return void
	 */
	public function setPropertiesAsArray($arrProperties)
	{
		if (is_array($arrProperties))
		{
			if (array_key_exists("id", $arrProperties))
			{
				unset($arrProperties["id"]);
			}

			$this->arrProperties = $arrProperties;
		}
	}

	/**
	 * Check if this model have any properties.
	 *
	 * @return boolean true if any property has been stored, false otherwise.
	 */
	public function hasProperties()
	{
		if (count($this->arrProperties) != 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get an iterator for this model's property values.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->arrProperties);
	}

	/**
	 * Sets the provider name in the model.
	 *
	 * NOTE: this is intended to be used by the data provider only and not by any user.
	 * Changing this by hand may cause unexpected behaviour. So DO NOT USE IT.
	 * For this reason, this method is not interfaced, as only the data provider knows how
	 * to set itself to the model.
	 *
	 * @param string $strProviderName The name of the corresponding data provider.
	 *
	 * @return void
	 */
	public function setProviderName($strProviderName)
	{
		$this->strProviderName = $strProviderName;
	}

	/**
	 * Return the data provider name.
	 *
	 * @return string the name of the corresponding data provider.
	 */
	public function getProviderName()
	{
		return $this->strProviderName;
	}
}