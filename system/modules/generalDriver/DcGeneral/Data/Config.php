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

use DcGeneral\Data\Interfaces\Config as ConfigInterface;

class Config implements ConfigInterface
{
	/**
	 * The id of the element to be retrieved.
	 *
	 * @var mixed
	 */
	protected $mixId = null;

	protected $arrIds = array();
	protected $blnIdOnly = false;
	protected $intStart = 0;
	protected $intAmount = 0;
	protected $arrFilter = null;
	protected $arrSearch = null;
	protected $arrSorting = null;
	protected $arrFields = null;
	protected $arrData = array();

	/**
	 * Create object.
	 *
	 * Private as only the driver shall know how to instantiate.
	 */
	private function __construct()
	{
		return $this;
	}

	/**
	 * Static constructor.
	 *
	 * @todo: do we want to keep this behaviour? Third party will not know the correct class anyway.
	 *
	 * @return Config
	 */
	public static function init()
	{
		return new static();
	}

	/**
	 * Get specific id.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->mixId;
	}

	/**
	 * Set specific id.
	 *
	 * @param mixed $mixId
	 *
	 * @return Config
	 */
	public function setId($mixId)
	{
		$this->mixId = $mixId;

		return $this;
	}

	/**
	 * Get list of specific ids to be retrieved.
	 *
	 * @return array
	 */
	public function getIds()
	{
		return $this->arrIds;
	}

	/**
	 * Set list of specific ids to be retrieved.
	 *
	 * @param array $arrIds The list of ids to be retrieved.
	 *
	 * @return Config
	 */
	public function setIds($arrIds)
	{
		$this->arrIds = $arrIds;

		return $this;
	}

	/**
	 * Return flag if only ids should be returned.
	 *
	 * @return boolean
	 */
	public function getIdOnly()
	{
		return $this->blnIdOnly;
	}

	/**
	 * Set flag for return id only.
	 *
	 * @param boolean $blnIdOnly Boolean flag to determine that only Ids shall be returned when calling fetchAll().
	 *
	 * @return bool
	 */
	public function setIdOnly($blnIdOnly)
	{
		$this->blnIdOnly = $blnIdOnly;

		return $this;
	}

	/**
	 * Get the offset to start with.
	 *
	 * This is the offset to use for pagination.
	 *
	 * @return integer
	 */
	public function getStart()
	{
		return $this->intStart;
	}

	/**
	 * Set the offset to start with.
	 *
	 * This is the offset to use for pagination.
	 *
	 * @param integer $intStart Number of first element to return.
	 *
	 * @return Config
	 */
	public function setStart($intStart)
	{
		$this->intStart = $intStart;

		return $this;
	}

	/**
	 * Get the limit for results.
	 *
	 * This is the amount of items to return for pagination.
	 *
	 * @return integer
	 */
	public function getAmount()
	{
		return $this->intAmount;
	}

	/**
	 * Set the limit for results.
	 *
	 * This is the amount of items to return for pagination.
	 *
	 * @param integer $intAmount
	 *
	 * @return Config
	 */
	public function setAmount($intAmount)
	{
		$this->intAmount = $intAmount;

		return $this;
	}

	/**
	 * Get the list with filter options.
	 *
	 * @return array
	 */
	public function getFilter()
	{
		return $this->arrFilter;
	}

	/**
	 * Set the list with filter options.
	 *
	 * @param array $arrFilter The array containing the filter values.
	 *
	 * @return Config
	 */
	public function setFilter($arrFilter)
	{
		$this->arrFilter = $arrFilter;

		return $this;
	}

	/**
	 * Get the list of all defined sortings.
	 *
	 * The returning array will be of 'property name' => 'ASC|DESC' nature.
	 *
	 * @return array
	 */
	public function getSorting()
	{
		return $this->arrSorting;
	}

	/**
	 * Set the list of all defined sortings.
	 *
	 * The array must be of 'property name' => 'ASC|DESC' nature.
	 *
	 * @param array $arrSorting The sorting array to use.
	 *
	 * @return array
	 */
	public function setSorting($arrSorting)
	{
		$this->arrSorting = $arrSorting;

		return $this;
	}

	/**
	 * Get the list of fields to be retrieved.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->arrFields;
	}

	/**
	 * Set the list of fields to be retrieved.
	 *
	 * @param array $arrFields Array of property names.
	 *
	 * @return Config
	 */
	public function setFields($arrFields)
	{
		$this->arrFields = $arrFields;

		return $this;
	}

	// TODO: make a property bag out of this.
	/**
	 * Get the additional information.
	 *
	 * @param string $strKey The name of the information to retrieve.
	 *
	 * @return mixed || null
	 */
	public function get($strKey)
	{
		if (isset($this->arrData[$strKey]))
		{
			return $this->arrData[$strKey];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Set the additional information.
	 *
	 * @param string $strKey   The name of the information to retrieve.
	 *
	 * @param mixed  $varValue The value to store.
	 *
	 * @return Config
	 */
	public function set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;

		return $this;
	}
}
