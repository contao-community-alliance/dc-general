<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Callbacks;

use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\OperationInterface;

// FIXME: remove System inheritance.
class ContaoStyleCallbacks extends \System implements CallbacksInterface
{

	/**
	 * The DC
	 *
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
	 * Execute the passed callbacks.
	 *
	 * The returned array will hold all result values from all via $varCallbacks defined callbacks.
	 *
	 * @param mixed $varCallbacks Either the name of an HOOK defined in $GLOBALS['TL_HOOKS'] or an array of
	 *                            array('Class', 'method') pairs.
	 *
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
	 * Call the customer label callback.
	 *
	 * @param ModelInterface  $objModelRow The current model for which the label shall get generated for.
	 *
	 * @param string $mixedLabel  The label string (as defined in DCA).
	 *
	 * @param array  $args        The arguments for the label string.
	 *
	 * @return string
	 */
	public function labelCallback(ModelInterface $objModelRow, $mixedLabel, $args)
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

			return $this->$strClass->$strMethod($objModelRow->getPropertiesAsArray(), $mixedLabel, $this->objDC, $args);
		}

		return null;
	}

	/**
	 * Call the button callback for the regular operations.
	 *
	 * @param ModelInterface   $objModelRow          The current model instance for which the button shall be
	 *                                      generated.
	 *
	 * @param array   $arrOperation         The operation for which a button shall be generated
	 *                                      (excerpt from DCA).
	 *
	 * @param string  $strLabel             The label for the button.
	 *
	 * @param string  $strTitle             The title for the button.
	 *
	 * @param array   $arrAttributes        Attributes for the generated button.
	 *
	 * @param string  $strTable             The dataprovider name of the view.
	 *
	 * @param array   $arrRootIds           The root ids
	 *
	 * @param array   $arrChildRecordIds    Ids of the direct children to the model in $objModelRow.
	 *
	 * @param boolean $blnCircularReference TODO: document parameter $blnCircularReference
	 *
	 * @param string  $strPrevious          TODO: document parameter $strPrevious
	 *
	 * @param string  $strNext              TODO: document parameter $strNext
	 *
	 * @return string|null
	 */
	public function buttonCallback($objModelRow, $arrOperation, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
	{
		if ($arrOperation instanceof OperationInterface)
		{
			/** @var \DcGeneral\DataDefinition\OperationInterface $arrOperation */
			$strHref     = $arrOperation->getHref();
			$strIcon     = $arrOperation->getIcon();
			$arrCallback = $arrOperation->getCallback();
		}
		else
		{
			$strHref     = $arrOperation['href'];
			$strIcon     = $arrOperation['icon'];
			$arrCallback = $arrOperation['button_callback'];
		}

		// Check Callback.
		if (is_array($arrCallback))
		{
			trigger_error('Deprecated callback system in use - please change to the event based system.', E_USER_DEPRECATED);

			$strClass = $arrCallback[0];
			$strMethod = $arrCallback[1];

			$this->import($strClass);

			return $this->$strClass->$strMethod(
				$objModelRow ? $objModelRow->getPropertiesAsArray() : null,
				$strHref,
				$strLabel,
				$strTitle,
				$strIcon,
				$arrAttributes,
				$strTable,
				$arrRootIds,
				$arrChildRecordIds,
				$blnCircularReference,
				$strPrevious,
				$strNext
			);
		}

		return null;
	}

	/**
	 * Call the button callback for the global operations.
	 *
	 * @param string $strLabel      Label for the button.
	 *
	 * @param string $strTitle      Title for the button.
	 *
	 * @param array  $arrAttributes Attributes for the button
	 *
	 * @param string $strTable      Name of the current data provider.
	 *
	 * @param array  $arrRootIds    Ids of the root elements in the data provider.
	 *
	 * @return string|null
	 */
	public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['button_callback']))
		{
			trigger_error('Deprecated callback system in use - please change to the event based system.', E_USER_DEPRECATED);

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
	 * @param array         $row           Array with current data
	 *
	 * @param string        $table         Tablename
	 *
	 * @param bool          $cr            TODO: document parameter $cr
	 *
	 * @param array         $objClipboard Clipboard informations
	 *
	 * @param string        $previous     TODO: document parameter $previous
	 *
	 * @param string        $next         TODO: document parameter $next
	 *
	 * @return string
	 */
	public function pasteButtonCallback($row, $table, $cr, $objClipboard, $previous, $next)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		// Check Callback
		if (is_array($arrDCA['list']['sorting']['paste_button_callback']))
		{
			$strClass = $arrDCA['list']['sorting']['paste_button_callback'][0];
			$strMethod = $arrDCA['list']['sorting']['paste_button_callback'][1];

			$this->import($strClass);
			return $this->$strClass->$strMethod($this->objDC, $row, $table, $cr, $objClipboard, $previous, $next);
		}

		return false;
	}

	/**
	 * Call the header callback.
	 *
	 * @param array $arrAdd TODO: document parameter $arrAdd
	 *
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
	 * Call the child record callback.
	 *
	 * @param ModelInterface $objModel TODO: document parameter $objModel
	 *
	 * @return string|null
	 */
	public function childRecordCallback(ModelInterface $objModel)
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
	 * Call the options callback for given the field.
	 *
	 * @param string $strField Name of the field for which to call the options callback.
	 *
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
	 * Call the onrestore callback.
	 *
	 * @param integer $intID      ID of current dataset.
	 *
	 * @param string  $strTable   Name of current Table.
	 *
	 * @param array   $arrData    Array with all Data.
	 *
	 * @param integer $intVersion Version which was restored.
	 *
	 * @return void
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
	 * Call the load callback.
	 *
	 * @param string $strField Name of the field for which to call the load callback.
	 *
	 * @param mixed $varValue  Current value to be transformed.
	 *
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
	 * Call onload_callback (e.g. to check permissions).
	 *
	 * @return void
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
	 * Call the group callback.
	 *
	 * @param type  $group TODO: document parameter $group
	 *
	 * @param type  $mode  TODO: document parameter $mode
	 *
	 * @param type  $field TODO: document parameter $field
	 *
	 * @param ModelInterface $objModelRow
	 *
	 * @return type  TODO: document result
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
			$currentGroup = $this->$strClass->$strMethod(
				$currentGroup,
				$mode,
				$field,
				is_object($objModelRow) ? $objModelRow->getPropertiesAsArray() : $objModelRow,
				$this
			);

			if ($currentGroup == null)
			{
				$group = $currentGroup;
			}
		}

		return $group;
	}

	/**
	 * Call the save callback for a widget.
	 *
	 * @param array $arrConfig Configuration of the widget.
	 *
	 * @param mixed $varNew    The new value that shall be transformed.
	 *
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
	 * Call ondelete_callback.
	 *
	 * @return void
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
	 * Call the onsubmit_callback.
	 *
	 * @return void
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
	 * Call the oncreate_callback.
	 *
	 * @param mixed $insertID  The id from the new record.
	 *
	 * @param array $arrRecord The new record.
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
	 * @param InterfaceGeneralModelInterface $objModel The model that has been updated.
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
	 * Get the current palette.
	 *
	 * @param array $arrPalette The current palette.
	 *
	 * @return array The modified palette.
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
	 *
	 * NOTE: the fact that this method has been called does not mean the values of the model have been changed
	 * it merely just tells "we will load a model (from memory or database) and update it's properties with
	 * those from the POST data".
	 *
	 * After the model has been updated, the onModelUpdateCallback will get triggered.
	 *
	 * @param ModelInterface $objModel The model that will get updated.
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
	 * @param ModelInterface $objModel The model that has been updated.
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

	public function generateBreadcrumb()
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['presentation']['breadcrumb_callback'];

		if (!is_array($arrCallback) || count($arrCallback) == 0)
		{
			return null;
		}

		// Get data from callback
		$strClass = $arrCallback[0];
		$strMethod = $arrCallback[1];

		// FIXME: implement this some better way.
		$objCallback = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
		$arrReturn = $objCallback->$strMethod($this->objDC);

		// Check if we have a result with elements
		if (!is_array($arrReturn) || count($arrReturn) == 0)
		{
			return null;
		}

		return $arrReturn;
	}

	/**
	 * Call the custom filter callback for a field.
	 *
	 * Return true if the processing has been successfully applied, false otherwise.
	 * NOTE: GeneralController will stop further processing of the given field if true has been returned and will
	 * continue to process filtering if false has been returned.
	 *
	 * @param string $strField   The field to filter.
	 *
	 * @param array  $arrSession The currently saved values of the filter session.
	 *
	 * @return bool
	 */
	public function customFilterCallback($strField, $arrSession)
	{
		// Load DCA
		$arrDCA = $this->objDC->getDCA();

		$stopProcessing = false;
		if ($callbacks = $arrDCA['fields'][$strField]['filterCallback'])
		{
			foreach ($callbacks as $callback)
			{
				$this->import($callback[0]);
				$stopProcessing = $this->$callback[0]->$callback[1]($strField, $arrSession, $this->objDC) || $stopProcessing;
			}
		}
		return $stopProcessing;
	}
}
