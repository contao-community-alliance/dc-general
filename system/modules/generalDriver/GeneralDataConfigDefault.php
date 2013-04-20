<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralDataConfigDefault implements InterfaceGeneralDataConfig
{

	protected $intId = null;
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
	 * Create object
	 * 
	 * @return GeneralDataConfigDefault 
	 */
	private function __construct()
	{
		return $this;
	}

	public static function init()
	{
		return new GeneralDataConfigDefault();
	}

	/**
	 * Get specific id
	 * 
	 * @return integer 
	 */
	public function getId()
	{
		return $this->intId;
	}

	/**
	 * Set specific id
	 * 
	 * @param integer $intId
	 * @return GeneralDataConfigDefault
	 */
	public function setId($intId)
	{
		$this->intId = $intId;

		return $this;
	}

	/**
	 * Get specific ids
	 * 
	 * @return array 
	 */
	public function getIds()
	{
		return $this->arrIds;
	}

	/**
	 * Set specific ids
	 * 
	 * @param array $arrIds 
	 */
	public function setIds($arrIds)
	{
		$this->arrIds = $arrIds;

		return $this;
	}

	/**
	 * Return flag if only ids should be returned
	 * 
	 * @return boolean
	 */
	public function getIdOnly()
	{
		return $this->blnIdOnly;
	}

	/**
	 * Set flag for return id only
	 * 
	 * @return boolean
	 * @return GeneralDataConfigDefault
	 */
	public function setIdOnly($blnIdOnly)
	{
		$this->blnIdOnly = $blnIdOnly;

		return $this;
	}

	/**
	 * Get the offset to start with
	 * 
	 * @return integer 
	 */
	public function getStart()
	{
		return $this->intStart;
	}

	/**
	 * Set the offset to start with
	 * 
	 * @param integer $intStart
	 * @return GeneralDataConfigDefault
	 */
	public function setStart($intStart)
	{
		$this->intStart = $intStart;

		return $this;
	}

	/**
	 * Get the limit for results 
	 * 
	 * @return integer 
	 */
	public function getAmount()
	{
		return $this->intAmount;
	}

	/**
	 * Set the limit for results
	 * 
	 * @param integer $intAmount
	 * @return GeneralDataConfigDefault
	 */
	public function setAmount($intAmount)
	{
		$this->intAmount = $intAmount;

		return $this;
	}

	/**
	 * Get the list with filter options
	 * 
	 * @return array 
	 */
	public function getFilter()
	{
		return $this->arrFilter;
	}

	/**
	 * Set the list with filter options
	 * 
	 * @return array
	 * @return GeneralDataConfigDefault
	 */
	public function setFilter($arrFilter)
	{
		$this->arrFilter = $arrFilter;

		return $this;
	}

	/**
	 * Get the list with all sortings
	 * 
	 * @return string[string] all columns with direction colname => ASC|DESC
	 */
	public function getSorting()
	{
		return $this->arrSorting;
	}

	/**
	 * Set the list with all sortings
	 * 
	 * @return GeneralDataConfigDefault
	 */
	public function setSorting($arrSorting)
	{
		$this->arrSorting = $arrSorting;

		return $this;
	}

	/**
	 * Get the nessessary fields
	 * 
	 * @return array 
	 */
	public function getFields()
	{
		return $this->arrFields;
	}

	/**
	 * Set the nessessary fields
	 *  
	 * @param array $arrFields
	 * @return GeneralDataConfigDefault
	 */
	public function setFields($arrFields)
	{
		$this->arrFields = $arrFields;

		return $this;
	}

	/**
	 * Get the additional information
	 * 
	 * @param string $strKey
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
	 * Set the additional information
	 * 
	 * @param string $strKey
	 * @param mixed $varValue
	 * @return GeneralDataConfigDefault
	 */
	public function set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;

		return $this;
	}
}