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

class PhpNativeCallbacks extends \System implements CallbacksInterface
{

	/**
	 * The DC
	 *
	 * @var DC_General
	 */
	private $objDC;

	/**
	 * {@inheritdoc}
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDC()
	{
		return $this->objDC;
	}

	/**
	 * {@inheritdoc}
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
			if (is_callable($arrCallback))
			{
				$arrResults[] = call_user_func_array($arrCallback, $arrArgs);
			}
		}

		return $arrResults;
	}

	/**
	 * {@inheritdoc}
	 */
	public function labelCallback(ModelInterface $objModelRow, $mixedLabel, $args)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();
		$arrCallback = $arrDCA['list']['label']['label_callback'];

		// Check Callback
		if (is_callable($arrCallback))
		{
			if (version_compare(VERSION, '2.10', '>'))
			{
				return call_user_func(
					$arrCallback,
					$objModelRow,
					$mixedLabel,
					$this->getDC(),
					$args
				);
			}
			else
			{
				return call_user_func(
					$arrCallback,
					$objModelRow,
					$mixedLabel,
					$this->getDC()
				);
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buttonCallback($objModelRow, $arrOperation, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
	{
		// Check Callback
		if (is_callable($arrOperation['button_callback']))
		{
			return call_user_func(
				$arrOperation['button_callback'],
				$objModelRow,
				$arrOperation['href'],
				$strLabel,
				$strTitle,
				$arrOperation['icon'],
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
	 * {@inheritdoc}
	 */
	public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Check Callback
		if (is_callable($arrDCA['button_callback']))
		{
			return call_user_func(
				$arrDCA['button_callback'],
				$arrDCA['href'],
				$strLabel,
				$strTitle,
				$arrDCA['icon'],
				$arrAttributes,
				$strTable,
				$arrRootIds
			);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pasteButtonCallback($row, $table, $cr, $childs, $previous, $next)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Check Callback
		if (is_callable($arrDCA['list']['sorting']['paste_button_callback']))
		{
			return call_user_func(
				$arrDCA['list']['sorting']['paste_button_callback'],
				$this->objDC,
				$row,
				$table,
				$cr,
				$childs,
				$previous,
				$next
			);
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function headerCallback($arrAdd)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();
		$arrCallback = $arrDCA['list']['sorting']['header_callback'];

		if (is_callable($arrCallback))
		{
			return call_user_func(
				$arrCallback,
				$arrAdd,
				$this->getDC()
			);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function childRecordCallback(ModelInterface $objModel)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();
		$arrCallback = $arrDCA['list']['sorting']['child_record_callback'];

		if (is_callable($arrCallback))
		{
			return call_user_func(
				$arrCallback,
				$objModel
			);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function optionsCallback($strField)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();
		$arrCallback = $arrDCA['fields'][$strField]['options_callback'];

		// Check Callback
		if (is_callable($arrCallback))
		{
			return call_user_func(
				$arrCallback,
				$this->getDC()
			);
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onrestoreCallback($intID, $strTable, $arrData, $intVersion)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Check Callback
		if (is_array($arrDCA['config']['onrestore_callback']))
		{
			foreach ($arrDCA['config']['onrestore_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					call_user_func(
						$callback,
						$intID,
						$strTable,
						$arrData,
						$intVersion
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadCallback($strField, $varValue)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();
		$arrCallbacks = $arrDCA['fields'][$strField]['load_callback'];

		// Load Callback
		if (is_array($arrCallbacks))
		{
			foreach ($arrCallbacks as $arrCallback)
			{
				if (is_callable($arrCallback))
				{
					$varValue = call_user_func(
						$arrCallback,
						$varValue,
						$this->getDC()
					);
				}
			}

			return $varValue;
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onloadCallback()
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Check Callback
		if (is_array($arrDCA['config']['onload_callback']))
		{
			foreach ($arrDCA['config']['onload_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					call_user_func(
						$callback,
						$this->getDC()
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function groupCallback($group, $mode, $field, $objModelRow)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		$currentGroup = $group;

		// Check Callback
		if (is_callable($arrDCA['list']['label']['group_callback']))
		{
			$currentGroup = call_user_func(
				$arrDCA['list']['label']['group_callback'],
				$currentGroup,
				$mode,
				$field,
				$objModelRow,
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
	 * {@inheritdoc}
	 */
	public function saveCallback($arrConfig, $varNew)
	{
		if (is_array($arrConfig['save_callback']))
		{
			foreach ($arrConfig['save_callback'] as $arrCallback)
			{
				if (is_callable($arrCallback)) {
					$varNew = call_user_func(
						$arrCallback,
						$varNew,
						$this->getDC()
					);
				}
			}
		}

		return $varNew;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ondeleteCallback()
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call ondelete_callback
		if (is_array($arrDCA['config']['ondelete_callback']))
		{
			foreach ($arrDCA['config']['ondelete_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					call_user_func(
						$callback,
						$this->getDC()
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onsubmitCallback()
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		if (is_array($arrDCA['config']['onsubmit_callback']))
		{
			foreach ($arrDCA['config']['onsubmit_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					call_user_func(
						$callback,
						$this->getDC()
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function oncreateCallback($insertID, $arrRecord)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['oncreate_callback']))
		{
			foreach ($arrDCA['config']['oncreate_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					call_user_func(
						$callback,
						$this->getDC()->getTable(),
						$insertID,
						$arrRecord,
						$this->getDC()
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseRootPaletteCallback($arrPalette)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['parseRootPalette_callback']))
		{
			foreach ($arrDCA['config']['parseRootPalette_callback'] as $callback)
			{
				if (is_callable($callback))
				{
					$mixReturn = call_user_func(
						$callback,
						$this->getDC(),
						$arrPalette
					);

					if (is_array($mixReturn))
					{
						$arrPalette = $mixReturn;
					}
				}
			}
		}

		return $arrPalette;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onModelBeforeUpdateCallback($objModel)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['onmodel_beforeupdate']))
		{
			foreach ($arrDCA['config']['onmodel_beforeupdate'] as $callback)
			{
				if (is_callable($callback)) {
					call_user_func(
						$callback,
						$objModel,
						$this->getDC()
					);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onModelUpdateCallback($objModel)
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		// Call the oncreate_callback
		if (is_array($arrDCA['config']['onmodel_update']))
		{
			foreach ($arrDCA['config']['onmodel_update'] as $callback)
			{
				if (is_callable($callback)) {
					call_user_func(
						$callback,
						$objModel,
						$this->getDC()
					);
				}
			}
		}
	}

	public function generateBreadcrumb()
	{
		// Load DCA
		$arrDCA = $this->getDC()->getDCA();

		if (is_array($arrDCA['list']['presentation']['breadcrumb_callback']))
		{
			if (is_callable($arrDCA['list']['presentation']['breadcrumb_callback'])) {
				return call_user_func(
					$arrDCA['list']['presentation']['breadcrumb_callback'],
					$this->getDC()
				);
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
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
				if (is_callable($callback)) {
					$stopProcessing = call_user_func(
						$callback,
						$strField,
						$arrSession,
						$this->objDC
					);
				}
			}
		}
		return $stopProcessing;
	}
}
