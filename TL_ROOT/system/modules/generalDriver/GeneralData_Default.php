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
class GeneralData_Default implements InterfaceGeneralData
{
    // Vars --------------------------------------------------------------------

    /**
     * Name of current source
     * @var string 
     */
    protected $strSource = null;

    /**
     * Database
     * @var Database 
     */
    protected $objDatabase = null;

    // Constructor and co ------------------------------------------------------

    public function __construct(array $arrConfig)
    {
        // Check Vars
        if (!isset($arrConfig["source"]))
        {
            throw new Excpetion("Missing table name.");
        }

        // Init Vars
        $this->strSource = $arrConfig["source"];

        // Init Helper
        $this->objDatabase = Database::getInstance();
    }

    // Functions ---------------------------------------------------------------

    public function delete($item)
    {
        // Not impl now       
    }

    /**
     * Fetch an empty single record (new item).
     * 
     * @return InterfaceGeneralModel
     */
    public function getEmptyModel()
    {
        return new GeneralModel_Default();
    }
    
    /**
     * Fetch an empty single collection (new item).
     * 
     * @return InterfaceGeneralModel
     */
    public function getEmptyCollection()
    {
        return new GeneralCollection_Default();
    }

    /**
     * Fetch a single record by id.
     * 
     * @param int ID
     * 
     * @return InterfaceGeneralModel
     */    
    public function fetch($intId)
    {
        $arrResult = $this->objDatabase
                ->prepare("SELECT * FROM $this->strSource WHERE id=?")
                ->execute($intId)
                ->fetchAllAssoc();

        if (count($arrResult) == 0)
        {
            return null;
        }

        $objModel = $this->getEmptyModel();

        foreach ($arrResult[0] as $key => $value)
        {
            $objModel->setProperty($key, $value);
        }

        return $objModel;
    }

    /**
     * Fetch all records (optional limited).
     * 
     * @param bool $blnIdOnly if true, only the ids are returned, if false real models are returned.
     * @param int $intStart optional offset to start retrival from
     * @param int $intAmount optional limit to limit the amount of retrieved items
     * @param array $arrFilter a list with filter options
     * @param array $arrSorting a list with all sortings
     * 
     * @return InterfaceGeneralCollection
     */    
    public function fetchAll($blnIdOnly = false, $intStart = 0, $intAmount = 0, $arrFilter = null, $arrSorting = null)
    {
        $boolSetWhere = FALSE;
        
        $query = "SELECT " . (($blnIdOnly) ? "id" : "*") . " FROM " . $this->strSource;

        if (!is_null($arrFilter))
        {
            foreach($arrFilter AS $key => $mixedFilter)
            {
                if(is_array($mixedFilter))
                {
                    $query .= " WHERE " . $key . " IN(" . implode(',', $mixedFilter) . ")";
                }
                unset($arrFilter[$key]);
            } 
            
            if(count($arrFilter) > 0)
            {
                $query .= (($boolSetWhere) ? " WHERE " : " AND ") . implode(' AND ', $arrFilter);
            }
        }
        
        if(!is_null($arrSorting))
        {
            $strSortOrder = '';
            
            foreach($arrSorting AS $key => $mixedField)
            {
                if(is_array($mixedField))
                {
                    if($mixedField['action'] == 'findInSet')
                    {
                        $arrSorting[$key] = $this->Database->findInSet($mixedField['field'], $mixedField['keys']);
                    }
                }
                
                if($key === 'sortOrder')
                {
                    $strSortOrder = $mixedField;
                    unset($arrSorting[$key]);
                }
            }
            
            $query .= " ORDER BY " . implode(', ', $arrSorting) . $strSortOrder;
        }       
        
        $arrResult = $this->objDatabase
                ->prepare($query)
                ->limit($intAmount, $intStart)
                ->execute()
                ->fetchAllAssoc();
        
        if (count($arrResult) == 0)
        {
            return $this->getEmptyCollection();
        }
        
        $objCollection = $this->getEmptyCollection();
        foreach ($arrResult as $key => $arrValue)
        {
            $objModel = $this->getEmptyModel();
            foreach ($arrValue as $k => $v)
            {
                $objModel->setProperty($k, $v);
            }
            
            $objCollection->add($objModel);
        }
        
        return $objCollection;
    }

    public function fetchEach($ids)
    {
        
    }

    public function getCount($arrFilter = array())
    {
        
    }

    public function getVersions($intID)
    {
        $arrVersion = $this->objDatabase
                ->prepare('SELECT tstamp, version, username, active FROM tl_version WHERE fromTable = ? AND pid = ? ORDER BY version DESC')
                ->execute($this->strSource, $intID)
                ->fetchAllAssoc();


        if (count($arrVersion) == 0)
        {
            return null;
        }

        $objCollection = $this->getEmptyCollection();

        foreach ($arrVersion as $versionValue)
        {
            $objReturn = $this->getEmptyModel();

            foreach ($versionValue as $key => $value)
            {
                $objReturn->setProperty($key, $value);
            }

            $objCollection->add($objReturn);
        }

        return $objCollection;
    }

    public function isUniqueValue($strField, $varNew)
    {
        $objUnique = $this->objDatabase
                ->prepare('SELECT * FROM ' . $this->strSource . ' WHERE ' . $strField . ' = ? ')
                ->execute($varNew);

        if ($objUnique->numRows == 0)
        {
            return true;
        }

        return false;
    }

    public function resetFallback($strField)
    {
        $this->objDatabase->query('UPDATE ' . $this->strSource . ' SET ' . $strField . ' = \'\'');
    }

    public function save(InterfaceGeneralModel $objItem, $recursive = false)
    {
        $arrSet = array();

        foreach ($objItem as $key => $value)
        {
            if ($key == "id")
            {
                continue;
            }

            $arrSet[$key] = $value;
        }


        if ($objItem->getProperty("id") == null || $objItem->getProperty("id") == "")
        {
            $this->objDatabase
                    ->prepare("INSERT INTO $this->strSource %s")
                    ->set($arrSet)
                    ->execute();
        }
        else
        {
            $this->objDatabase
                    ->prepare("UPDATE $this->strSource %s WHERE id=?")
                    ->set($arrSet)
                    ->execute($objItem->getProperty("id"));
        }
    }

    public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
    {
        foreach ($objItems as $key => $value)
        {
            $this->save($value);
        }
    }

    public function setVersion($intID, $strVersion)
    {
        $objData = $this->Database->
                prepare('SELECT * FROM tl_version WHERE fromTable = ? AND pid = ? AND version = ? ')
                ->limit(1)
                ->execute($this->strSource, $intID, $strVersion);

        if (!$objData->numRows)
        {
            return;
        }

        $arrData = deserialize($objData->data);

        if (!is_array($arrData))
        {
            return;
        }

        $this->Database
                ->prepare('UPDATE ' . $objData->fromTable . ' %s WHERE id = ?')
                ->set($arrData)
                ->execute($intID);

        $this->Database
                ->prepare('UPDATE tl_version SET active=\'\' WHERE pid = ?')
                ->execute($intID);

        $this->Database
                ->prepare('UPDATE tl_version SET active = 1 WHERE pid = ? AND version = ?')
                ->execute($intID, $strVersion);

        $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $strVersion, $intID, $this->strSource), 'DC_Table edit()', TL_GENERAL);

        // ToDo: Add Callback
//        $this->executeCallbacks(
//                $this->arrDCA['config']['onrestore_callback'], $this->intId, $this->strSource, $arrData, $strVersion
//        );
    }

    /**
     * Check if the value exists in the table
     * 
     * @return boolean 
     */    
    public function fieldExists($strField)
    {
        return $this->objDatabase->fieldExists($strField, $this->strSource);
    }

}

?>
