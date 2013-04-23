<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralCallbackDefault extends System implements InterfaceGeneralCallback
{

	/**
	 * The DC
	 * @var DC_General
	 */
	private $objDC;

	/**
	 * Set the DC
	 *
	 * @param DC_General $objDC
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
	}

	/**
	 * get the DC
	 *
	 * @return DC_General $objDC
	 */
	public function getDC()
	{
		return $this->objDC;
	}

	/**
	 * Exectue a callback
	 *
	 * @param array $varCallbacks
	 * @return array
	 */
	public function executeCallbacks($varCallbacks)
	{
		if ($varCallbacks === null)
		{
			return array();
		}

		if (is_string($varCallbacks))
		{
			$varCallbacks = $GLOBALS['TL_HOOKS'][$varCallbacks];
		}

		if (!is_array($varCallbacks))
		{
			return array();
		}

		$arrArgs = array_slice(func_get_args(), 1);
		$arrResults = array();

		foreach ($varCallbacks as $arrCallback)
		{
			if (is_array($arrCallback))
			{
				$this->import($arrCallback[0]);
				$arrCallback[0] = $this->{$arrCallback[0]};
				$arrResults[] = call_user_func_array($arrCallback, $arrArgs);
			}
		}

		return $arrResults;
	}

	/**
	 * Call the customer label callback
	 *
	 * @param InterfaceGeneralModel $objModelRow
	 * @param string $mixedLabel
	 * @param array $args
	 * @return string
	 */
	public function labelCallback(InterfaceGeneralModel $objModelRow, $mixedLabel, $args)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['label']['label_callback'];

		// Check Callback
		if (is_array($arrCallback))
		{
			$strClass = $arrCallback[0];
			$strMethod = $arrCallback[1];

			$this->import($strClass);

			if (version_compare(VERSION, '2.10', '>'))
			{
				return $this->$strClass->$strMethod($objModelRow->getPropertiesAsArray(), $mixedLabel, $this->objDC, $args);
			}
			else
			{
				return $this->$strClass->$strMethod($objModelRow->getPropertiesAsArray(), $mixedLabel, $this->objDC);
			}
		}

		return null;
	}

	/**
	 * Call the button callback for the regular operations
	 *
	 * @param InterfaceGeneralModel $objModelRow
	 * @param array $arrDCA
	 * @param string $strLabel
	 * @param string $strTitle
	 * @param array $arrAttributes
	 * @param string $strTable
	 * @param array $arrRootIds
	 * @param array $arrChildRecordIds
	 * @param boolean $blnCircularReference
	 * @param string $strPrevious
	 * @param string $strNext
	 * @return string|null
	 */
	public function buttonCallback($objModelRow, $arrOperation, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
	{
		// Check Callback
		if (is_array($arrOperation['button_callback']))
		{
			$strClass = $arrOperation['button_callback'][0];
			$strMethod = $arrOperation['button_callback'][1];

			$this->import($strClass);

			return $this->$strClass->$strMethod(
					$objModelRow->getPropertiesAsArray(), $arrOperation['href'], $strLabel, $strTitle, $arrOperation['icon'], $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext
			);
		}

		return null;
	}

	/**
	 * Call the button callback for the global operations
	 *
	 * @param array $arrDCA
	 * @param str $strLabel
	 * @param str $strTitle
	 * @param array $arrAttributes
	 * @param string $strTable
	 * @param array $arrRootIds
	 * @return string|null
	 */
	public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['button_callback']))
		{
			$strClass = $arrDCA['button_callback'][0];
			$strMethod = $arrDCA['button_callback'][1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($arrDCA['href'], $strLabel, $strTitle, $arrDCA['icon'], $arrAttributes, $strTable, $arrRootIds);
		}

		return null;
	}

	/**
	 * Call the button callback for the paste operations
	 *
	 * @param DataContainer $dc DataContainer or DC_General
	 * @param array $row Array with current data
	 * @param string $table Tablename
	 * @param unknown $cr K.A.
	 * @param array $childs Clipboard informations
	 * @param unknown $previous K.A.
	 * @param unknown $next K.A.
	 *
	 * @return string
	 */
	public function pasteButtonCallback($dc, $row, $table, $cr, $childs, $previous, $next)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['list']['sorting']['paste_button_callback']))
		{
			$strClass = $arrDCA['list']['sorting']['paste_button_callback'][0];
			$strMethod = $arrDCA['list']['sorting']['paste_button_callback'][1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($dc, $row, $table, $cr, $childs, $previous, $next);
		}

		return false;
	}

	/**
	 * Call the header callback
	 *
	 * @param array $arrAdd
	 * @return array|null
	 */
	public function headerCallback($arrAdd)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['sorting']['header_callback'];

		if (is_array($arrCallback))
		{
			$strClass = $arrCallback[0];
			$strMethod = $arrCallback[1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($arrAdd, $this->objDC);
		}

		return null;
	}

	/**
	 * Call the child record callback
	 *
	 * @param InterfaceGeneralModel $objModel
	 * @return string|null
	 */
	public function childRecordCallback(InterfaceGeneralModel $objModel)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['sorting']['child_record_callback'];

		if (is_array($arrCallback) && count($arrCallback))
		{
			$strClass = $arrCallback[0];
			$strMethod = $arrCallback[1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($objModel->getPropertiesAsArray());
		}

		return null;
	}

	/**
	 * Call the options callback for given the fields
	 *
	 * @param string $strField
	 * @return array|null
	 */
	public function optionsCallback($strField)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallback = $arrDCA['fields'][$strField]['options_callback'];

		// Check Callback
		if (is_array($arrCallback))
		{
			$strClass = $arrCallback[0];
			$strMethod = $arrCallback[1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($this->objDC);
		}

		return null;
	}

	/**
	 * Trigger the onrestore_callback
	 *
	 * @param int $intID ID of current dataset
	 * @param string $strTable Name of current Table
	 * @param array $arrData Array with all Data
	 * @param int $intVersion Version which was restored
	 */
	public function onrestoreCallback($intID, $strTable, $arrData, $intVersion)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['config']['onrestore_callback']))
		{
			foreach ($arrDCA['config']['onrestore_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($intID, $strTable, $arrData, $intVersion);
				}
			}
		}
	}

	/**
	 * Call the load callback
	 *
	 * @param string $strField
	 * @param mixed $varValue
	 * @return mixed|null
	 */
	public function loadCallback($strField, $varValue)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallbacks = $arrDCA['fields'][$strField]['load_callback'];

		// Load Callback
		if (is_array($arrCallbacks))
		{
			foreach ($arrCallbacks as $arrCallback)
			{
				if (is_array($arrCallback))
				{
					$strClass = $arrCallback[0];
					$strMethod = $arrCallback[1];

					$this->import($strClass);
					$varValue = $this->$strClass->$strMethod($varValue, $this->objDC);
				}
			}

			return $varValue;
		}

		return null;
	}

	/**
	 * Call onload_callback (e.g. to check permissions)
	 *
	 * @param string $strTable name of current table
	 */
	public function onloadCallback()
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['config']['onload_callback']))
		{
			foreach ($arrDCA['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this->objDC);
				}
			}
		}
	}

	/**
	 * Call the group callback
	 *
	 * @param type $group
	 * @param type $mode
	 * @param type $field
	 * @param InterfaceGeneralModel $objModelRow
	 * @return type
	 */
	public function groupCallback($group, $mode, $field, $objModelRow)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		$currentGroup = $group;

		// Check Callback
		if (is_array($arrDCA['list']['label']['group_callback']))
		{
			$strClass = $arrDCA['list']['label']['group_callback'][0];
			$strMethod = $arrDCA['list']['label']['group_callback'][1];

			$this->import($strClass);
			$currentGroup = $this->$strClass->$strMethod($currentGroup, $mode, $field, $objModelRow->getPropertiesAsArray(), $this);

			if ($currentGroup == null)
			{
				$group = $currentGroup;
			}
		}

		return $group;
	}

	/**
	 * Call the save callback for a widget
	 *
	 * @param array $arrConfig Configuration of the widget
	 * @param mixed $varNew New Value
	 * @return mixed
	 */
	public function saveCallback($arrConfig, $varNew)
	{
		if (is_array($arrConfig['save_callback']))
		{
			foreach ($arrConfig['save_callback'] as $arrCallback)
			{
				$this->import($arrCallback[0]);
				$varNew = $this->$arrCallback[0]->$arrCallback[1]($varNew, $this->objDC);
			}
		}

		return $varNew;
	}

	/**
	 * Call ondelete_callback
	 */
	public function ondeleteCallback()
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Call ondelete_callback
		if (is_array($arrDCA['config']['ondelete_callback']))
		{
			foreach ($arrDCA['config']['ondelete_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this->objDC);
				}
			}
		}
	}

	/**
	 * Call the onsubmit_callback
	 */
	public function onsubmitCallback()
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		if (is_array($arrDCA['config']['onsubmit_callback']))
		{
			foreach ($arrDCA['config']['onsubmit_callback'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->objDC);
			}
		}
	}

	/**
	 * Call the oncreate_callback
	 *
	 * @param mixed $insertID The id from the new record
	 * @param array $arrRecord the new record
	 *
	 * @return void
	 */
	public function oncreateCallback($insertID, $arrRecord)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['oncreate_callback']))
		{
			foreach ($arrDCA['config']['oncreate_callback'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->objDC->getTable(), $insertID, $arrRecord, $this->objDC);
			}
		}
	}

	/**
	 * Call the onsave_callback
	 *
	 * @param InterfaceGeneralModel $objModel The model that has been updated.
	 *
	 * @return void
	 */
	public function onsaveCallback($objModel)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['onsave_callback']))
		{
			foreach ($arrDCA['config']['onsave_callback'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($objModel, $this->getDC());
			}
		}
	}


	/**
	 * Get the current pallette
	 *
	 * @param DC_General $objDC
	 * @param array $arrPalette
	 */
	public function parseRootPaletteCallback($arrPalette)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['parseRootPalette_callback']))
		{
			foreach ($arrDCA['config']['parseRootPalette_callback'] as $callback)
			{
				$this->import($callback[0]);
				$mixReturn = $this->$callback[0]->$callback[1]($this->objDC, $arrPalette);

				if (is_array($mixReturn))
				{
					$arrPalette = $mixReturn;
				}
			}
		}

		return $arrPalette;
	}

	/**
	 * Call the onmodel_beforeupdate.
	 * NOTE: the fact that this method has been called does not mean the values of the model have been changed
	 * it merely just tells "we have loaded a model (from memory or database) and updated it's properties with
	 * those from the POST data".
	 *
	 * @param InterfaceGeneralModel $objModel The model that has been updated.
	 *
	 * @return void
	 */
	public function onModelBeforeUpdateCallback($objModel)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['onmodel_beforeupdate']))
		{
			foreach ($arrDCA['config']['onmodel_beforeupdate'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($objModel, $this->objDC);
			}
		}
	}

	/**
	 * Call the onmodel_update.
	 * NOTE: the fact that this method has been called does not mean the values of the model have been changed
	 * it merely just tells "we have loaded a model (from memory or database) and updated it's properties with
	 * those from the POST data".
	 *
	 * @param InterfaceGeneralModel $objModel The model that has been updated.
	 *
	 * @return void
	 */
	public function onModelUpdateCallback($objModel)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['onmodel_update']))
		{
			foreach ($arrDCA['config']['onmodel_update'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($objModel, $this->objDC);
			}
		}
	}

}

?>
