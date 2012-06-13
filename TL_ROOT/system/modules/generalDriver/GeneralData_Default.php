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
class GeneralData_Default implements InterfaceGeneralData
{
    // Vars --------------------------------------------------------------------

    /**
     * Name of current table
     * @var string 
     */
    protected $strTabel = null;

    /**
     * Database
     * @var Database 
     */
    protected $objDatabase = null;

    // Constructor and co ------------------------------------------------------

    public function __construct(array $arrConfig)
    {
        // Check Vars
        if (!isset($arrConfig["table"]))
        {
            throw new Excpetion("Missing table name.");
        }

        // Init Vars
        $this->strTabel = $arrConfig["table"];

        // Init Helper
        $this->objDatabase = Database::getInstance();
    }

    // Functions ---------------------------------------------------------------

    public function delete($item)
    {
        // Not impl now       
    }

    public function fetch($id)
    {
        
    }

    public function fetchAll($blnIdOnly = false, $intStart = 0, $intAmount = 0, array $arrFilter = array())
    {
        
    }

    public function fetchEach(array $ids)
    {
        
    }

    public function getCount(array $arrFilter = array())
    {
        
    }

    public function getVersions($intID)
    {
        $objVersion = $this->objDatabase
                ->prepare('SELECT tstamp, version, username, active FROM tl_version WHERE fromTable = ? AND pid = ? ORDER BY version DESC')
                ->execute($this->strTable, $intID);

        if (!$objVersion->numRows)
        {
            return;
        }
        
        $objReturn = new GeneralModel_Default();
        
        foreach ($objVersion->fetchAllAssoc() as $key => $value)
        {
            $objReturn->setProperty($key, $value);
        }

        return $objReturn;
    }

    public function isUniqueValue($strField, $varNew)
    {
        $objUnique = $this->objDatabase
                ->prepare('SELECT * FROM ' . $this->strTable . ' WHERE ' . $strField . ' = ? ')
                ->execute($varNew);

        if ($objUnique->numRows == 0)
        {
            return true;
        }

        return false;
    }

    public function resetFallback($strField)
    {
        $this->objDatabase->query('UPDATE ' . $this->strTable . ' SET ' . $strField . ' = \'\'');
    }

    public function save($item, bool $recursive = false)
    {
        
    }

    public function saveEach(Collection $items, bool $recursive = false)
    {
        
    }

    public function setVersion($intID, $strVersion)
    {
        $objData = $this->Database->
                prepare('SELECT * FROM tl_version WHERE fromTable = ? AND pid = ? AND version = ? ')
                ->limit(1)
                ->execute($this->strTable, $intID, $strVersion);

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

        $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $strVersion, $intID, $this->strTable), 'DC_Table edit()', TL_GENERAL);

        // ToDo: Add Callback
//        $this->executeCallbacks(
//                $this->arrDCA['config']['onrestore_callback'], $this->intId, $this->strTable, $arrData, $strVersion
//        );
    }

}

?>
