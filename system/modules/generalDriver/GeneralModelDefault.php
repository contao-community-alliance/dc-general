<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralModelDefault implements InterfaceGeneralModel
{

	/**
	 * A list with all Properties.
	 *
	 * @param array $strPropertyName
	 */
	protected $arrProperties = array();
	protected $mixID = null;

	/**
	 * A list with all Properties.
	 *
	 * @param array $strPropertyName
	 */
	protected $arrMetaInformation = array();

	/**
	 * The name of the corresponding data provider.
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
	 * Get the id for this modell.
	 *
	 * @return string The ID for this modell.
	 */
	public function getID()
	{
		return $this->mixID;
	}

	/**
	 * @see InterfaceGeneralModel::getProperty()
	 *
	 * @param String $strPropertyName
	 * @return null
	 */
	public function getProperty($strPropertyName)
	{
		if($strPropertyName == 'id')
		{
			return $this->getID();
		}

		if (key_exists($strPropertyName, $this->arrProperties))
		{
			return $this->arrProperties[$strPropertyName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * @see InterfaceGeneralModel::getPropertiesAsArray()
	 */
	public function getPropertiesAsArray()
	{
		$arrArray       = $this->arrProperties;
		$arrArray["id"] = $this->mixID;

		return $arrArray;
	}

	/**
	 * @see InterfaceGeneralModel::getMeta()
	 *
	 * @param string $strMetaName the meta information to retrieve.
	 *
	 * @return mixed|null the set meta information or null if undefined.
	 */
	public function getMeta($strMetaName)
	{
		if (key_exists($strMetaName, $this->arrMetaInformation))
		{
			return $this->arrMetaInformation[$strMetaName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Set the id for this modell.
	 * This works only once.
	 *
	 * @param mixed $mixID Could be a integer, string or anything else
	 */
	public function setID($mixID)
	{
		if ($this->mixID == null)
		{
			$this->mixID = $mixID;
		}
	}

	/**
	 * @see InterfaceGeneralModel::setProperty()
	 *
	 * @param String $strPropertyName
	 * @param mixed $varValue
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		$this->arrProperties[$strPropertyName] = $varValue;
	}

	/**
	 * @see InterfaceGeneralModel::setPropertiesAsArray()
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
	 * @see InterfaceGeneralModel::setMeta()
	 *
	 * @param string $strMetaName the meta information name.
	 *
	 * @param mixed $varValue the meta information to store.
	 *
	 * @return void
	 */
	public function setMeta($strMetaName, $varValue)
	{
		$this->arrMetaInformation[$strMetaName] = $varValue;
	}

	/**
	 * @see InterfaceGeneralModel::hasProperties()
	 *
	 * @return boolean
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
	 * Get a iterator for this collection
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->arrProperties);
	}

	/**
	 * Sets the provider name in the model.
	 * NOTE: this is intended to be used by the data provider only and not by any user.
	 * Changing this by hand may cause unexpected behaviour. So DO NOT USE IT.
	 * For this reason, this method is not interfaced, as only the data provider knows how
	 * to set itself to the model.
	 *
	 * @param string the name of the corresponding data provider.
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