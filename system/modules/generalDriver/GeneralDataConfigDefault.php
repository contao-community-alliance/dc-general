<?php

if (!defined('TL_ROOT'))
	die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @see InterfaceGeneralData
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
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
	 * @return array 
	 */
	public function getSorting()
	{
		return $this->arrSorting;
	}

	/**
	 * Set the list with all sortings
	 * 
	 * @return array
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

	/**
	 * Get the current configurationset with search parameter
	 * 
	 * @return array $arrSeach
	 */
	public function getSearch()
	{
		return $this->arrSearch;
	}

	/**
	 * <p>Set a configurationset with search parameter</p>
	 * <p>Array Description:<br/>
	 * Mode - When the mode is unknown or unsupportet the system will use the Default mode.<br/>
	 * See in the DCGE.php. There are some const vars vor mode, calles DP_MODE_*
	 * </p>
	 * <pre>
	 * array(
	 * 	[key] => array(
	 * 		'field'	=> [Name of field in DataProvider]
	 * 		'mode'  => [Mode for the search. Params are: 'like', 'REGEX', '']
	 * 		'value'	=> [The value for the search]
	 * 	)
	 * );
	 * </pre>
	 * 
	 * @param array $arrSeach
	 * @return GeneralDataConfigDefault
	 */
	public function setSearch($arrSeach)
	{
		$this->arrSearch = (array) $arrSeach;

		return $this;
	}

}