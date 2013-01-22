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
class GeneralDataDefault implements InterfaceGeneralData
{
	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Vars
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

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

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Constructor and co
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	public function __construct()
	{
		// Init Helper
		$this->objDatabase = Database::getInstance();
		$this->objUser = BackendUser::getInstance();
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Getter | Setter
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Set base config with source and other neccesary prameter
	 *
	 * @param array $arrConfig
	 * @throws Exception
	 */
	public function setBaseConfig(array $arrConfig)
	{
		// Check Vars
		if (!isset($arrConfig["source"]))
		{
			throw new Exception("Missing table name.");
		}

		// Init Vars
		$this->strSource = $arrConfig["source"];
	}

	/**
	 * Return empty config object
	 *
	 * @return InterfaceGeneralDataConfig
	 */
	public function getEmptyConfig()
	{
		return GeneralDataConfigDefault::init();
	}

	/**
	 * Fetch an empty single record (new item).
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getEmptyModel()
	{
		$objModel = new GeneralModelDefault();
		$objModel->setProviderName($this->strSource);
		return $objModel;
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

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Helper Functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Build the field list
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return string
	 */
	protected function buildFieldQuery($objConfig)
	{
		$strFields = '*';

		if ($objConfig->getIdOnly())
		{
			$strFields = 'id';
		}
		else if (!is_null($objConfig->getFields()))
		{
			$strFields = implode(', ', $objConfig->getFields());

			if (!stristr($strFields, 'DISTINCT'))
			{
				$strFields = 'id, ' . $strFields;
			}
		}

		return $strFields;
	}

	/**
	 * Combine a filter in standard filter array notation.
	 * Supported operations are:
	 * operation      needed arguments     argument type.
	 * AND
	 *                'childs'             array
	 * OR
	 *                'childs'             array
	 * =
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * >
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * <
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * IN
	 *                'property'           string (the name of a property)
	 *                'values'             array of literal
	 *
	 * LIKE
	 * 				  'property'		   string (the name of a property)
	 * 			      'value'              literal - Wildcards * (Many) ? (One)
	 *
	 * @param array $arrFilters the filter to be combined to a valid SQL filter query.
	 *
	 * @return string the combined WHERE clause.
	 */
	protected function calculateSubfilter($arrFilter, array &$arrParams)
	{
		if (!is_array($arrFilter))
		{
			throw new Exception('Error Processing subfilter: ' . var_export($arrFilter, true), 1);
		}

		switch ($arrFilter['operation'])
		{
			case 'AND':
			case 'OR':
				if (!$arrFilter['childs'])
				{
					return '';
				}
				$arrCombine = array();
				foreach ($arrFilter['childs'] as $arrChild)
				{
					$arrCombine[] = $this->calculateSubfilter($arrChild, $arrParams);
				}
				return implode(sprintf(' %s ', $arrFilter['operation']), $arrCombine);

			case '=':
			case '>':
			case '<':
				$arrParams[] = $arrFilter['value'];
				return sprintf('(%s %s ?)', $arrFilter['property'], $arrFilter['operation']);

			case 'IN':
				$arrParams = array_merge($arrParams, array_values($arrFilter['values']));
				$strWildcards = rtrim(str_repeat('?,', count($arrFilter['values'])), ',');
				return sprintf('(%s IN (%s))', $arrFilter['property'], $strWildcards);

			case 'LIKE':
				$strWildcards = str_replace(array('*', '?'), array('%', '_'), $arrFilter['value']);
				$arrParams[] = $strWildcards;
				return sprintf('(%s LIKE "?")', $arrFilter['property'], $strWildcards);

			default:
				throw new Exception('Error processing filter array ' . var_export($arrFilter, true), 1);
		}
	}

	/**
	 * Build the Where
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 * @param array $arrParams
	 */
	protected function buildWhereQuery($objConfig, array &$arrParams = null)
	{
		$arrParams || $arrParams = array();

		$arrQuery = array();

		$arrQuery['filter'] = $this->buildFilterQuery($objConfig, $arrParams);

		$arrQuery = array_filter($arrQuery, 'strlen');

		return count($arrQuery) ? ' WHERE ' . implode(' AND ', $arrQuery) : '';
	}

	/**
	 * Build the Where
	 *
	 * @param GeneralDataConfigDefault $objConfig,
	 *
	 * @return string
	 */
	protected function buildFilterQuery($objConfig, array &$arrParams = null)
	{
		$arrParams || $arrParams = array();

		$strReturn = $this->calculateSubfilter(
				array(
			'operation' => 'AND',
			'childs' => $objConfig->getFilter()
				), $arrParams
		);

		// combine filter syntax.
		return $strReturn ? $strReturn : '';
	}

	/**
	 * Build the order by
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return string
	 */
	protected function buildSortingQuery($objConfig)
	{
		$arrSorting = $objConfig->getSorting();
		$strReturn = '';
		$arrFields = array();

		if (!is_null($arrSorting) && is_array($arrSorting) && count($arrSorting) > 0)
		{
			foreach ($arrSorting AS $strField => $strOrder)
			{
				if (!in_array($strOrder, array(DCGE::MODEL_SORTING_ASC, DCGE::MODEL_SORTING_DESC)))
				{
					$strOrder = DCGE::MODEL_SORTING_ASC;
				}

				$arrFields[] = $strField . ' ' . $strOrder;
			}

			$strReturn .= ' ORDER BY ' . implode(', ', $arrFields);
		}

		return $strReturn;
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Delete an item.
	 *
	 * @param int|string|InterfaceGeneralModel Id or the object itself, to delete
	 */
	public function delete($item)
	{
		if (is_numeric($item) || is_string($item))
		{
			// Insert undo
			$this->insertUndo('DELETE FROM ' . $this->strSource . ' WHERE id = ' . $item, 'SELECT * FROM ' . $this->strSource . '  WHERE id = ' . $item, $this->strSource);

			$this->objDatabase
					->prepare("DELETE FROM $this->strSource WHERE id=?")
					->execute($item);
		}
		else if (is_object($item) && $item instanceof InterfaceGeneralModel)
		{
			if (strlen($item->getID()) != 0)
			{
				// Insert undo
				$this->insertUndo('DELETE FROM ' . $this->strSource . ' WHERE id = ' . $item->getID(), 'SELECT * FROM ' . $this->strSource . '  WHERE id = ' . $item->getID(), $this->strSource);

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
	 * Fetch a single/first record by id/filter.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralModel
	 */
	public function fetch(GeneralDataConfigDefault $objConfig)
	{
		if ($objConfig->getId() != null)
		{
			$strQuery = "SELECT " . $this->buildFieldQuery($objConfig) . " FROM $this->strSource WHERE id = ?";

			$arrResult = $this->objDatabase
					->prepare($strQuery)
					->execute($objConfig->getId())
					->fetchAllAssoc();
		}
		else
		{
			// Build SQL
			$query = "SELECT " . $this->buildFieldQuery($objConfig) . " FROM " . $this->strSource;
			$query .= $this->buildWhereQuery($objConfig, $arrParams);
			$query .= $this->buildSortingQuery($objConfig);

			// Execute db query
			$arrResult = $this->objDatabase
					->prepare($query)
					->limit(1, 0)
					->execute($arrParams)
					->fetchAllAssoc();
		}

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
	public function fetchAll(GeneralDataConfigDefault $objConfig)
	{
		// Build SQL
		$query = "SELECT " . $this->buildFieldQuery($objConfig) . " FROM " . $this->strSource;
		$query .= $this->buildWhereQuery($objConfig, $arrParams);
		$query .= $this->buildSortingQuery($objConfig);

		// Execute db query
		$objDatabaseQuery = $this->objDatabase->prepare($query);

		if ($objConfig->getAmount() != 0)
		{
			$objDatabaseQuery->limit($objConfig->getAmount(), $objConfig->getStart());
		}

		$arrResult = $objDatabaseQuery->executeUncached($arrParams)->fetchAllAssoc();

		if ($objConfig->getIdOnly())
		{
			$arrIds = array();
			foreach ($arrResult as $intId)
			{
				$arrIds[] = $intId['id'];
			}

			return $arrIds;
		}

		$objCollection = $this->getEmptyCollection();

		if (count($arrResult) == 0)
		{
			return $objCollection;
		}

		foreach ($arrResult as $key => $arrValue)
		{
			$objModel = $this->getEmptyModel();
			foreach ($arrValue as $key => $value)
			{
				if ($key == "id")
				{
					$objModel->setID($value);
				}

				$objModel->setProperty($key, $value);
			}

			$objCollection->add($objModel);
		}

		return $objCollection;
	}

	/**
	 * Return the amount of total items.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return int
	 */
	public function getCount(GeneralDataConfigDefault $objConfig)
	{
		$query = "SELECT COUNT(*) AS count FROM " . $this->strSource;
		$query .= $this->buildWhereQuery($objConfig, $arrParams);

		$objCount = $this->objDatabase
				->prepare($query)
				->execute($arrParams);

		return $objCount->count;
	}

	public function isUniqueValue($strField, $varNew, $intId = null)
	{
		$objUnique = $this->objDatabase
				->prepare('SELECT * FROM ' . $this->strSource . ' WHERE ' . $strField . ' = ? ')
				->execute($varNew);

		if ($objUnique->numRows == 0)
		{
			return true;
		}

		if (($objUnique->numRows == 1) && ($objUnique->id == $intId))
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

			if (is_array($value))
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

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Version Functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

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
				->execute($objModel->getID(), $this->strSource);

		$mixNewVersion = intval($objCount->mycount) + 1;

		$mixData = $objModel->getPropertiesAsArray();
		$mixData["id"] = $objModel->getID();
		$mixData = serialize($mixData);

		$arrInsert = array();
		$arrInsert['pid'] = $objModel->getID();
		$arrInsert['tstamp'] = time();
		$arrInsert['version'] = $mixNewVersion;
		$arrInsert['fromTable'] = $this->strSource;
		$arrInsert['username'] = $strUsername;
		$arrInsert['data'] = $mixData;

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

		if ($objVersionID->numRows == 0)
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

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Undo
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
	{
		// Load row
		$arrResult = $this->objDatabase
				->prepare($strSaveSQL)
				->executeUncached()
				->fetchAllAssoc();

		// Check if we have a result
		if (count($arrResult) == 0)
		{
			return;
		}

		// Save information in array
		$arrSave = array();
		foreach ($arrResult as $value)
		{
			$arrSave[$strTable][] = $value;
		}

		$strPrefix = '<span style="color:#b3b3b3; padding-right:3px;">(DC General)</span>';

		// Write into undo
		$this->objDatabase
				->prepare("INSERT INTO tl_undo (pid, tstamp, fromTable, query, affectedRows, data) VALUES (?, ?, ?, ?, ?, ?)")
				->execute($this->objUser->id, time(), $strTable, $strPrefix . $strSourceSQL, count($arrSave[$strTable]), serialize($arrSave));
	}

}

?>
