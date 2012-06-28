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
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
interface InterfaceGeneralData
{
    
    /**
     * 
     */
    public function __construct(array $arrConfig);

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
     * @param int ID
     * 
     * @return InterfaceGeneralModel
     */
    public function fetch($id);

    /**
     * Fetch multiple records by ids.
     * 
     * @param array A list of id's
     * 
     * @return InterfaceGeneralCollection
     */
    public function fetchEach($ids);

    /**
     * Fetch all records (optional limited).
     * 
     * @param GeneralDataConfigDefault $objConfig
     * 
     * @return InterfaceGeneralCollection
     */
    public function fetchAll($objConfig);

    /**
     * Return the amount of total items.
     *
     * @param array $arrFilter a list with filter options
     * 
     * @return int
     */
    public function getCount($arrFilter = array());

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
     * Documentation: 
     *      Evaluation - unique => If true the field value cannot be saved if it exists already.
     * 
     * @return boolean
     */
    public function isUniqueValue($strField, $varNew);
    
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

?>
