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

use DcGeneral\Data\Interfaces\Driver as DriverInterface;
use DcGeneral\Data\Interfaces\Config;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\Interfaces\Model;
use DcGeneral\Data\DefaultCollection as DataCollection;
use DcGeneral\Data\Config as DataConfig;
use DcGeneral\Data\Model as DataModel;


class Driver implements DriverInterface
{
	/**
	 * Name of current source.
	 *
	 * @var string
	 */
	protected $strSource = null;

	/**
	 * The Database instance.
	 *
	 * @todo: Use DI container for database instance.
	 *
	 * @var \Database
	 */
	protected $objDatabase = null;


	public function __construct()
	{
		// Init Helper
		$this->objDatabase = \Database::getInstance();
	}

	/**
	 * Set base config with source and other necessary parameter.
	 *
	 * @param array $arrConfig The configuration to use.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when no source has been defined.
	 */
	public function setBaseConfig(array $arrConfig)
	{
		// Check configuration.
		if (!isset($arrConfig["source"]))
		{
			throw new \RuntimeException("Missing table name.");
		}

		$this->strSource = $arrConfig["source"];
	}

	/**
	 * Return an empty configuration object.
	 *
	 * @return Config
	 */
	public function getEmptyConfig()
	{
		return DataConfig::init();
	}

	/**
	 * Fetch an empty single record (new model).
	 *
	 * @return Model
	 */
	public function getEmptyModel()
	{
		$objModel = new DataModel();
		$objModel->setProviderName($this->strSource);
		return $objModel;
	}

	/**
	 * Fetch an empty single collection (new model list).
	 *
	 * @return CollectionInterface
	 */
	public function getEmptyCollection()
	{
		return new DataCollection();
	}

	/**
	 * Build the field list.
	 *
	 * Returns all values from $objConfig->getFields() as comma separated list.
	 *
	 * @param Config $objConfig The configuration to use.
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
	 *                'property'           string (the name of a property)
	 *                'value'              literal - Wildcards * (Many) ? (One)
	 *
	 * @param array $arrFilter  The filter to be combined to a valid SQL filter query.
	 *
	 * @param array &$arrParams The query parameters will get stored into this array.
	 *
	 * @return string The combined WHERE conditions.
	 *
	 * @throws \RuntimeException if an invalid filter entry is encountered.
	 */
	protected function calculateSubfilter($arrFilter, array &$arrParams)
	{
		if (!is_array($arrFilter))
		{
			throw new \RuntimeException('Error Processing sub filter: ' . var_export($arrFilter, true), 1);
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
				return sprintf('(%s LIKE ?)', $arrFilter['property'], $strWildcards);

			default:
				throw new \RuntimeException('Error processing filter array ' . var_export($arrFilter, true), 1);
		}
	}

	/**
	 * Build the WHERE clause for a configuration.
	 *
	 * @param Config $objConfig  The configuration to use.
	 *
	 * @param array  &$arrParams The query parameters will get stored into this array.
	 *
	 * @return string  The combined WHERE clause (including the word "WHERE").
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
	 * Build the WHERE conditions via calculateSubfilter()
	 *
	 * @param Config $objConfig  The configuration to use.
	 *
	 * @param array  &$arrParams The query parameters will get stored into this array.
	 *
	 * @return string The combined WHERE conditions.
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
	 * Build the order by part of a query.
	 *
	 * @param Config $objConfig
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

	/**
	 * Delete an item.
	 *
	 * The given value may be either integer, string or an instance of InterfaceGeneralModel
	 *
	 * @param mixed $item Id or the model itself, to delete.
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 *
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
			throw new \RuntimeException("ID missing or given object not of type 'InterfaceGeneralModel'.");
		}
	}

	/**
	 * Fetch a single or first record by id or filter.
	 *
	 * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
	 *
	 * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
	 *
	 * @param Config $objConfig
	 *
	 * @return Model
	 */
	public function fetch(Config $objConfig)
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
			$arrParams = array();
			// Build SQL.
			$query = "SELECT " . $this->buildFieldQuery($objConfig) . " FROM " . $this->strSource;
			$query .= $this->buildWhereQuery($objConfig, $arrParams);
			$query .= $this->buildSortingQuery($objConfig);

			// Execute db query.
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
	 * Fetch all records (optional filtered, sorted and limited).
	 *
	 * @param Config $objConfig
	 *
	 * @return CollectionInterface
	 */
	public function fetchAll(Config $objConfig)
	{
		$arrParams = array();
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

		foreach ($arrResult as $arrValue)
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
	 * Retrieve all unique values for the given property.
	 *
	 * The result set will be an array containing all unique values contained in the data provider.
	 * Note: this only re-ensembles really used values for at least one data set.
	 *
	 * The only information being interpreted from the passed config object is the first property to fetch and the
	 * filter definition.
	 *
	 * @param Config $objConfig   The filter config options.
	 *
	 * @return CollectionInterface
	 *
	 * @throws \RuntimeException if improper values have been passed (i.e. not exactly one field requested).
	 */
	public function getFilterOptions(Config $objConfig)
	{
		$arrProperties = $objConfig->getFields();
		$strProperty = $arrProperties[0];

		if (count($arrProperties) <> 1)
		{
			throw new \RuntimeException('objConfig must contain exactly one property to be retrieved.');
		}

		$arrParams = array();

		$objValues = $this->objDatabase
			->prepare(sprintf('SELECT DISTINCT(%s) FROM %s %s',
				$strProperty,
				$this->strSource,
				$this->buildWhereQuery($objConfig, $arrParams)
			))
			->execute($arrParams);

		$objCollection = $this->getEmptyCollection();
		while ($objValues->next())
		{
			$objNewModel = $this->getEmptyModel();
			$objNewModel->setProperty($strProperty, $objValues->$strProperty);
			$objCollection->add($objNewModel);
		}

		return $objCollection;
	}

	/**
	 * Return the amount of total items (filtering may be used in the config).
	 *
	 * @param Config $objConfig
	 *
	 * @return int
	 */
	public function getCount(Config $objConfig)
	{
		$arrParams = array();

		$query = "SELECT COUNT(*) AS count FROM " . $this->strSource;
		$query .= $this->buildWhereQuery($objConfig, $arrParams);

		$objCount = $this->objDatabase
			->prepare($query)
			->execute($arrParams);

		return $objCount->count;
	}

	/**
	 * Check if the value is unique in table
	 *
	 * @param string $strField The field in which to test.
	 *
	 * @param mixed  $varNew   The value about to be saved.
	 *
	 * @param int    $intId    The (optional) id of the item currently in scope - pass null for new items.
	 *
	 * Documentation:
	 *      Evaluation - unique => If true the field value cannot be saved if it exists already.
	 *
	 * @return boolean
	 */
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

	/**
	 * Reset the fallback field.
	 *
	 * This clears the given property in all items in the table to an empty value.
	 *
	 * Documentation:
	 *      Evaluation - fallback => If true the field can only be assigned once per table.
	 *
	 * @param string $strField The field to reset.
	 *
	 * @return void
	 */
	public function resetFallback($strField)
	{
		$this->objDatabase->query('UPDATE ' . $this->strSource . ' SET ' . $strField . ' = \'\'');
	}

	/**
	 * Save an item to the database.
	 *
	 * If the item does not have an Id yet, the save operation will add it as a new row to the database and
	 * populate the Id of the model accordingly.
	 *
	 * @param Model $objItem   The model to save back.
	 *
	 * @return Model The passed model.
	 */
	public function save(Model $objItem)
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

	/**
	 * Save a collection of items to the database.
	 *
	 * @param CollectionInterface $objItems The collection containing all items to be saved.
	 *
	 * @return void
	 */
	public function saveEach(Collection $objItems)
	{
		foreach ($objItems as $value)
		{
			$this->save($value);
		}
	}

	/**
	 * Check if a property with the given name exists in the table.
	 *
	 * @param string $strField The name of the property to search.
	 *
	 * @return boolean
	 */
	public function fieldExists($strField)
	{
		return $this->objDatabase->fieldExists($strField, $this->strSource);
	}

	/**
	 * Return a model based of the version information.
	 *
	 * @param mixed $mixID      The ID of the item.
	 *
	 * @param mixed $mixVersion The ID of the version.
	 *
	 * @return Model
	 */
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
	 * Return a list with all versions for the row with the given Id.
	 *
	 * @param mixed   $mixID         The ID of the row.
	 *
	 * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
	 *                               returned.
	 *
	 * @return CollectionInterface
	 */
	public function getVersions($mixID, $blnOnlyActive = false)
	{
		if ($blnOnlyActive)
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

	/**
	 * Save a new version of a row.
	 *
	 * @param Model $objModel    The model for which a new version shall be created.
	 *
	 * @param string                $strUsername The username to attach to the version as creator.
	 *
	 * @return void
	 */
	public function saveVersion(Model $objModel, $strUsername)
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
	 * Set a version as active.
	 *
	 * @param mixed $mixID      The ID of the row.
	 *
	 * @param mixed $mixVersion The version number to set active.
	 *
	 * @return void
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
	 * Retrieve the current active version for a row.
	 *
	 * @param mixed $mixID The ID of the row.
	 *
	 * @return mixed The current version number of the requested row.
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
	 * Check if two models have the same values in all properties.
	 *
	 * @param Model $objModel1 The first model to compare.
	 *
	 * @param Model $objModel2 The second model to compare.
	 *
	 * @return boolean True - If both models are same, false if not.
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

	/**
	 * TODO: this is not in the interface yet and has to be documented.
	 *
	 * @param string $strSourceSQL
	 *
	 * @param string $strSaveSQL
	 *
	 * @param string $strTable
	 */
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
		$objUser   = \BackendUser::getInstance();

		// Write into undo
		$this->objDatabase
			->prepare("INSERT INTO tl_undo (pid, tstamp, fromTable, query, affectedRows, data) VALUES (?, ?, ?, ?, ?, ?)")
			->execute($objUser->id, time(), $strTable, $strPrefix . $strSourceSQL, count($arrSave[$strTable]), serialize($arrSave));
	}

}
