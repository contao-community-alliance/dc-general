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
class GeneralDataDefault implements InterfaceGeneralData
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

    // Getter | Setter ---------------------------------------------------------

    /**
     * Fetch an empty single record (new item).
     * 
     * @return InterfaceGeneralModel
     */
    public function getEmptyModel()
    {
        return new GeneralModelDefault();
    }

    /**
     * Fetch an empty single collection (new item).
     * 
     * @return InterfaceGeneralModel
     */
    public function getEmptyCollection()
    {
        return new GeneralCollectionDefault();
    }

    // Functions ---------------------------------------------------------------

    /**
     * Delete an item.
     * 
     * @param int|string|InterfaceGeneralModel Id or the object itself, to delete
     */
    public function delete($item)
    {
        if (is_numeric($item) || is_string($item))
        {
            $this->objDatabase
                    ->prepare("DELETE FROM $this->strSource WHERE id=?")
                    ->execute($item);
        }
        else if (is_object($item) && is_a($item, "InterfaceGeneralModel"))
        {
            if (strlen($item->getID()) != 0)
            {
                $this->objDatabase
                        ->prepare("DELETE FROM $this->strSource WHERE id=?")
                        ->execute($item->getID());
            }
        }
        else
        {
            throw new Exception("ID missing or given object not from type 'InterfaceGeneralModel'.");
        }
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
            if ($key == "id")
            {
                $objModel->setID($value);
            }

            $objModel->setProperty($key, $value);
        }

        return $objModel;
    }

    /**
     * Fetch all records (optional limited).
     * 
     * @param GeneralDataConfigDefault $objConfig
     * 
     * @return InterfaceGeneralCollection
     */
    public function fetchAll($objConfig)
    {        
        $boolSetWhere = true;
        $arrFilter = $objConfig->getFilter();
        $arrSorting = $objConfig->getSorting();

        $query = "SELECT " . (($objConfig->getIdOnly()) ? "id" : "*") . " FROM " . $this->strSource;

        
        
        if (!is_null($arrFilter))
        {
            foreach ($arrFilter AS $key => $mixedFilter)
            {
                if (is_array($mixedFilter))
                {
                    $query .= (($boolSetWhere) ? " WHERE " : " AND ") . $key . " IN(" . implode(',', $mixedFilter) . ")";
                    unset($arrFilter[$key]);
                    $boolSetWhere = false;
                }
            }

            if (count($arrFilter) > 0)
            {
                $query .= (($boolSetWhere) ? " WHERE " : " AND ") . implode(' AND ', $arrFilter);
                $boolSetWhere = false;
            }
        }

        if (!is_null($arrSorting) && is_array($arrSorting) && count($arrSorting) > 0)
        {
            $strSortOrder = '';

            foreach ($arrSorting AS $key => $mixedField)
            {
                if (is_array($mixedField))
                {
                    if ($mixedField['action'] == 'findInSet')
                    {
                        $arrSorting[$key] = $this->objDatabase->findInSet($mixedField['field'], $mixedField['keys']);
                    }
                }

                if ($key === 'sortOrder')
                {
                    $strSortOrder = $mixedField;
                    unset($arrSorting[$key]);
                }
            }

            $query .= " ORDER BY " . implode(', ', $arrSorting) . $strSortOrder;
        }

        $arrResult = $this->objDatabase
                ->prepare($query)
                ->limit($objConfig->getAmount(), $objConfig->getStart())
                ->execute()
                ->fetchAllAssoc();
       
        if($objConfig->getIdOnly())
        {
            return $arrResult;
        }

        $objCollection = $this->getEmptyCollection();

        if (count($arrResult) == 0)
        {
            return $objCollection;
        }

        foreach ($arrResult as $key => $arrValue)
        {
            $objModel = $this->getEmptyModel();
            foreach ($arrValue as $k => $v)
            {
                if ($key == "id")
                {
                    $objModel->setID($value);
                }

                $objModel->setProperty($k, $v);
            }

            $objCollection->add($objModel);
        }

        return $objCollection;
    }

    public function fetchEach($ids)
    {
        throw new Exception("Unsupported Operation: Not supported yet.");
    }

    public function getCount($arrFilter = array())
    {
        $boolSetWhere = true;

        $query = "SELECT COUNT(*) AS count FROM " . $this->strSource;

        if (!is_null($arrFilter))
        {
            foreach ($arrFilter AS $key => $mixedFilter)
            {
                if (is_array($mixedFilter))
                {
                    $query .= (($boolSetWhere) ? " WHERE " : " AND ") . $key . " IN(" . implode(',', $mixedFilter) . ")";
                    $boolSetWhere = false;
                }
                unset($arrFilter[$key]);
            }

            if (count($arrFilter) > 0)
            {
                $query .= (($boolSetWhere) ? " WHERE " : " AND ") . implode(' AND ', $arrFilter);
                $boolSetWhere = false;
            }
        }

        $objCount = $this->objDatabase
                ->prepare($query)
                ->execute();

        return $objCount->count;
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
            
            if(is_array($value))
            {
                $arrSet[$key] = serialize($value);
            }
            else
            {
                $arrSet[$key] = $value;
            }
        }


        if ($objItem->getID() == null || $objItem->getID() == "")
        {
            $objInsert = $this->objDatabase
                    ->prepare("INSERT INTO $this->strSource %s")
                    ->set($arrSet)
                    ->execute();

            if (strlen($objInsert->insertId) != 0)
            {
                $objItem->setID($objInsert->insertId);
            }
        }
        else
        {
            $this->objDatabase
                    ->prepare("UPDATE $this->strSource %s WHERE id=?")
                    ->set($arrSet)
                    ->execute($objItem->getID());
        }

        return $objItem;
    }

    public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
    {
        foreach ($objItems as $key => $value)
        {
            $this->save($value);
        }
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

    // Version Functions -------------------------------------------------------

    public function getVersion($mixID, $mixVersion)
    {
        $objVersion = $this->objDatabase
                ->prepare("SELECT * FROM tl_version WHERE pid=? AND version=? AND fromTable=?")
                ->execute($mixID, $mixVersion, $this->strSource);

        if ($objVersion->numRows == 0)
        {
            return null;
        }

        $arrData = deserialize($objVersion->data);

        if (!is_array($arrData) || count($arrData) == 0)
        {
            return null;
        }

        $objModell = $this->getEmptyModel();
        $objModell->setID($mixID);
        foreach ($arrData as $key => $value)
        {
            if ($key == "id")
            {
                continue;
            }

            $objModell->setProperty($key, $value);
        }

        return $objModell;
    }

    /**
     * Return a list with all versions for this row
     * 
     * @param mixed $mixID The ID of record
     * 
     * @return InterfaceGeneralCollection 
     */
    public function getVersions($mixID, $blnOnlyActve = false)
    {
        if ($blnOnlyActve)
        {
            $arrVersion = $this->objDatabase
                ->prepare('SELECT tstamp, version, username, active FROM tl_version WHERE fromTable = ? AND pid = ? AND active = 1')
                ->execute($this->strSource, $mixID)
                ->fetchAllAssoc();
        }
        else
        {
            $arrVersion = $this->objDatabase
                    ->prepare('SELECT tstamp, version, username, active FROM tl_version WHERE fromTable = ? AND pid = ? ORDER BY version DESC')
                    ->execute($this->strSource, $mixID)
                    ->fetchAllAssoc();
        }

        if (count($arrVersion) == 0)
        {
            return null;
        }

        $objCollection = $this->getEmptyCollection();

        foreach ($arrVersion as $versionValue)
        {
            $objReturn = $this->getEmptyModel();
            $objReturn->setID($mixID);

            foreach ($versionValue as $key => $value)
            {
                if ($key == "id")
                {
                    continue;
                }

                $objReturn->setProperty($key, $value);
            }

            $objCollection->add($objReturn);
        }

        return $objCollection;
    }

    public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
    {
        $objCount = $this->objDatabase
                ->prepare("SELECT count(*) as mycount FROM tl_version WHERE pid=? AND fromTable = ?")
                ->execute($objModel->getID(),  $this->strSource);

        $mixNewVersion = intval($objCount->mycount) + 1;
        
        $mixData       = $objModel->getPropertiesAsArray();
        $mixData["id"] = $objModel->getID();
        $mixData       = serialize($mixData);

        $arrInsert = array();
        $arrInsert['pid']       = $objModel->getID();
        $arrInsert['tstamp']    = time();
        $arrInsert['version']   = $mixNewVersion;
        $arrInsert['fromTable'] = $this->strSource;
        $arrInsert['username']  = $strUsername;
        $arrInsert['data']      = $mixData;

        $this->objDatabase->prepare('INSERT INTO tl_version %s')
                ->set($arrInsert)
                ->execute();

        $this->setVersionActive($objModel->getID(), $mixNewVersion);
    }

    /**
     * Set a Version as active. 
     * 
     * @param mix $mixID The ID of record
     * @param mix $mixVersion The ID of the Version
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $this->objDatabase
                ->prepare('UPDATE tl_version SET active=\'\' WHERE pid = ? AND fromTable = ?')
                ->execute($mixID, $this->strSource);

        $this->objDatabase
                ->prepare('UPDATE tl_version SET active = 1 WHERE pid = ? AND version = ? AND fromTable = ?')
                ->execute($mixID, $mixVersion, $this->strSource);
    }
    
    /**
     * Return the active version from a record
     * 
     * @param mix $mixID The ID of record
     * 
     * @return mix Version ID 
     */
    public function getActiveVersion($mixID)
    {
        $objVersionID = $this->objDatabase
                ->prepare("SELECT version FROM tl_version WHERE pid = ? AND fromTable = ? AND active = 1")
                ->execute($mixID, $this->strSource);
        
        if($objVersionID->numRows == 0)
        {
            return null;
        }
        
        return $objVersionID->version;
    }
    
    /**
     * Check if two models have the same properties
     * 
     * @param InterfaceGeneralModel $objModel1
     * @param InterfaceGeneralModel $objModel2
     * 
     * return boolean True - If both models are same, false if not
     */
    public function sameModels($objModel1, $objModel2)
    {
        foreach ($objModel1 as $key => $value)
        {
            if ($key == "id")
            {
                continue;
            }

            if (is_array($value))
            {
                if (!is_array($objModel2->getProperty($key)))
                {
                    return false;
                }

                if (serialize($value) != serialize($objModel2->getProperty($key)))
                {
                    return false;
                }
            }
            else if ($value != $objModel2->getProperty($key))
            {
                return false;
            }
        }

        return true;
    }

}

?>
