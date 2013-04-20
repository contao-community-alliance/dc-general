<?php

/**
 * PHP version 5
 * @package	   generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the MetaModel filter interface.
 *
 * @package	   generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class GeneralDataTableRowsAsRecords extends GeneralDataDefault
{
	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Getter | Setter
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	/**
	 * grouping column to use to tie rows together.
	 */
	protected $strGroupCol = 'pid';

	/**
	 * sorting column to sort the entries by.
	 */
	protected $strSortCol = '';

	/**
	 * Set base config with source and other neccesary prameter
	 *
	 * @param array $arrConfig
	 * @throws Excpetion
	 */
	public function setBaseConfig(array $arrConfig)
	{
		parent::setBaseConfig($arrConfig);

		if (!$arrConfig['group_column'])
		{
			throw new Exception('GeneralDataTableRowsAsRecords needs a grouping column.', 1);

		}
		$this->strGroupCol = $arrConfig['group_column'];

		if ($arrConfig['sort_column'])
		{
			$this->strSortCol = $arrConfig['sort_column'];
		}
	}

	protected function youShouldNotCallMe($strMethod)
	{
		throw new Exception(sprintf('Error, %s not available, as the data provider is intended for edit mode only.', $strMethod), 1);
	}


	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Functions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	/**
	 * Delete an item.
	 *
	 * @param int|string|InterfaceGeneralModel Id or the object itself, to delete
	 */
	public function delete($item)
	{
		$this->youShouldNotCallMe(__METHOD__);
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
		if (!$objConfig->getId())
		{
			throw new Exception("Error, no id passed, GeneralDataTableRowsAsRecords is only intended for edit mode.", 1);
		}

		$strQuery = sprintf('SELECT %s FROM %s WHERE %s=?', $this->buildFieldQuery($objConfig), $this->strSource, $this->strGroupCol);

		if ($this->strSortCol)
		{
			$strQuery .= ' ORDER BY ' . $this->strSortCol;
		}

		$objResult = $this->objDatabase
			->prepare($strQuery)
			->execute($objConfig->getId());

		$objModel = $this->getEmptyModel();
		if ($objResult->numRows)
		{
			$objModel->setProperty('rows', $objResult->fetchAllAssoc());
		}

		$objModel->setID($objConfig->getId());

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
		$this->youShouldNotCallMe(__METHOD__);
	}

	/**
	 * Fetch multiple records by ids.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function fetchEach(GeneralDataConfigDefault $objConfig)
	{
		$this->youShouldNotCallMe(__METHOD__);
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
		$this->youShouldNotCallMe(__METHOD__);
	}


	public function isUniqueValue($strField, $varNew, $intId = null)
	{
		$this->youShouldNotCallMe(__METHOD__);
	}

	public function resetFallback($strField)
	{
		$this->youShouldNotCallMe(__METHOD__);
	}

	/**
	 * Save a model to the database.
	 * In general, this method fetches the solely properts "rows" from the model and updates the local table against these contents.
	 * The parent id (id of the model) will get checked and reflected also for new items.
	 * When rows with duplicate ids are encountered (like from MCW for example), the dupes are inserted as new rows.
	 *
	 * @param InterfaceGeneralModel $objItem   the model to save.
	 *
	 * @param bool                  $recursive ignored as not relevant.
	 */
	public function save(InterfaceGeneralModel $objItem, $recursive = false)
	{
		$arrData = $objItem->getProperty('rows');
		if (!($objItem->getID() && $arrData))
		{
			throw new Exception('invalid input data in model.', 1);
		}

		$arrKeep = array();
		foreach($arrData as $i => $arrRow)
		{
			// TODO: add an option to restrict this to some allowed fields?
			$arrSQL = $arrRow;

			// update all.
			$intId = intval($arrRow['id']);

			// Work around the fact that multicolumnwizard does not clear any hidden fields when copying a dataset.
			// therefore we do consider any dupe as new dataset and save it accordingly.
			if (in_array($intId, $arrKeep))
			{
				$intId = 0;
				unset($arrSQL['id']);
			}

			if ($intId>0)
			{
				$this->objDatabase->prepare(sprintf('UPDATE tl_metamodel_dca_combine %%s WHERE id=? AND %s=?', $this->strGroupCol))
							   ->set($arrSQL)
							   ->execute($intId, $objItem->getId());
				$arrKeep[] = $intId;
			} else {
				// force group col value:
				$arrSQL[$this->strGroupCol] = $objItem->getId();
				$arrKeep[] = $this->objDatabase->prepare('INSERT INTO tl_metamodel_dca_combine %s')
							   ->set($arrSQL)
							   ->execute()
							   ->insertId;
			}
		}
		// house keeping, kill the rest.
		$this->objDatabase->prepare(sprintf('DELETE FROM  tl_metamodel_dca_combine WHERE %s=? AND id NOT IN (%s)', $this->strGroupCol, implode(',', $arrKeep)))
							   ->execute($objItem->getId());
		return $objItem;
	}

	public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
	{
		$this->youShouldNotCallMe(__METHOD__);
	}

	/**
	 * Check if the value exists in the table
	 *
	 * @return boolean
	 */
	public function fieldExists($strField)
	{
		return in_array($strField, array('tstamp'));
	}

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Version Functions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	public function getVersion($mixID, $mixVersion)
	{
		$this->youShouldNotCallMe(__METHOD__);
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
		// sorry, versioning not supported. :/
		return null;
	}

	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
	{
		$this->youShouldNotCallMe(__METHOD__);
	}

	/**
	 * Set a Version as active.
	 *
	 * @param mix $mixID The ID of record
	 * @param mix $mixVersion The ID of the Version
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		$this->youShouldNotCallMe(__METHOD__);
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
		$this->youShouldNotCallMe(__METHOD__);
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
		$this->youShouldNotCallMe(__METHOD__);
	}

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Undo
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
	{
		$this->youShouldNotCallMe(__METHOD__);
	}
}