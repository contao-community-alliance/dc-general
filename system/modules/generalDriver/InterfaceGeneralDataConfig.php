<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
interface InterfaceGeneralDataConfig
{
    public static function init();

    /**
     * Get specific id
     * 
     * @return integer 
     */
    public function getId();

    /**
     * Set specific id
     * 
     * @param integer $intId
     * @return GeneralDataConfigDefault
     */
    public function setId($intId);

    /**
     * Get specific ids
     * 
     * @return array 
     */
    public function getIds();

    /**
     * Set specific ids
     * 
     * @param array $arrIds 
     */
    public function setIds($arrIds);

    /**
     * Return flag if only ids should be returned
     * 
     * @return boolean
     */
    public function getIdOnly();

    /**
     * Set flag for return id only
     * 
     * @return boolean
     * @return GeneralDataConfigDefault
     */
    public function setIdOnly($blnIdOnly);

    /**
     * Get the offset to start with
     * 
     * @return integer 
     */
    public function getStart();

    /**
     * Set the offset to start with
     * 
     * @param integer $intStart
     * @return GeneralDataConfigDefault
     */
    public function setStart($intStart);

    /**
     * Get the limit for results 
     * 
     * @return integer 
     */
    public function getAmount();

    /**
     * Set the limit for results
     * 
     * @param integer $intAmount
     * @return GeneralDataConfigDefault
     */
    public function setAmount($intAmount);

    /**
     * Get the list with filter options
     * 
     * @return array 
     */
    public function getFilter();

    /**
     * Set the list with filter options
     * 
     * @return GeneralDataConfigDefault
     */
    public function setFilter($arrFilter);
    
    /**
     * Get the list with all sortings
     * 
     * @return array 
     */
    public function getSorting();

    /**
     * Set the list with all sortings
     * 
     * @return array
     * @return GeneralDataConfigDefault
     */
    public function setSorting($arrSorting);
    
    /**
     * Get the nessessary fields
     * 
     * @return array 
     */
    public function getFields();

    /**
     * Set the nessessary fields
     *  
     * @param array $arrFields
     * @return GeneralDataConfigDefault
     */
    public function setFields($arrFields);  

    /**
     * Get the additional information
     * 
     * @param string $strKey
     * @return mixed || null 
     */
    public function get($strKey);

    /**
     * Set the additional information
     * 
     * @param string $strKey
     * @param mixed $varValue
     * @return GeneralDataConfigDefault
     */
    public function set($strKey, $varValue);

}