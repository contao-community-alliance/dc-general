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
 * @see InterfaceGeneralView
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralViewDefault extends Controller implements InterfaceGeneralView
{
	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Vars
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	// Palettes/View vars ---------------------------
	protected $arrSelectors = array();
	protected $arrAjaxPalettes = array();
	protected $arrRootPalette = array();
	// Multilanguage vars ---------------------------
	protected $strCurrentLanguage;
	protected $blnMLSupport = false;
	// Overall Vars ---------------------------------
	protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>This function/view is not implemented.</div>";

	// Objects --------------------------------------

	/**
	 * Driver class
	 * @var DC_General
	 */
	protected $objDC = null;

	/**
	 * The current working model
	 * @var InterfaceGeneralModel
	 */
	protected $objCurrentModel = null;

	/**
	 * A list with all supported languages
	 * @var InterfaceGeneralCollection
	 */
	protected $objLanguagesSupported = null;

	/**
	 * Used by palette rendering.
	 *
	 * @var array
	 */
	protected $arrStack = array();

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Magic function
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 *  Getter & Setter
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	public function getDC()
	{
		return $this->objDC;
	}

	public function setDC($objDC)
	{
		$this->objDC = $objDC;
	}

	public function isSelector($strSelector)
	{
		return isset($this->arrSelectors[$strSelector]);
	}

	public function getSelectors()
	{
		return $this->arrSelectors;
	}

	public function isEmptyPalette()
	{
		return !count($this->arrRootPalette);
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 *  Core Support functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Check if the dataprovider is multilanguage.
	 * Save the current language and language array.
	 *
	 * @return void
	 */
	protected function checkLanguage()
	{
		$objDataProvider = $this->getDC()->getDataProvider();

		// Check if DP is multilanguage
		if ($this->getDC()->getDataProvider() instanceof InterfaceGeneralDataMultiLanguage)
		{
			$this->blnMLSupport = true;
			$this->objLanguagesSupported = $objDataProvider->getLanguages($this->getDC()->getId());
			$this->strCurrentLanguage = $objDataProvider->getCurrentLanguage();
		}
		else
		{
			$this->blnMLSupport = false;
			$this->objLanguagesSupported = null;
			$this->strCurrentLanguage = null;
		}
	}

	/**
	 * Load the current model from driver
	 */
	protected function loadCurrentModel()
	{
		$this->objCurrentModel = $this->getDC()->getCurrentModel();
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 *  Core function
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * @todo All
	 * @return Stirng
	 */
	public function copy()
	{
		return $this->notImplMsg;
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function copyAll()
	{
		return $this->notImplMsg;
	}

	/**
	 * @see edit()
	 * @return stirng
	 */
	public function create()
	{
		return $this->edit();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function cut()
	{
		return $this->notImplMsg;
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function cutAll()
	{
		return $this->notImplMsg;
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function delete()
	{
		return $this->notImplMsg;
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function move()
	{
		return $this->notImplMsg;
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function undo()
	{
		return $this->notImplMsg;
	}

	/**
	 * Generate the view for edit
	 *
	 * @return string
	 */
	public function edit()
	{
		// Load basic informations
		$this->checkLanguage();
		$this->loadCurrentModel();

		// Get all selectors
		$this->arrStack[] = $this->getDC()->getSubpalettesDefinition();
		$this->calculateSelectors($this->arrStack[0]);
		$this->parseRootPalette();

		include(TL_ROOT . '/system/config/languages.php');

		// ToDo: What is this $languages[$this->strCurrentLanguage];

		$objTemplate = new BackendTemplate('dcbe_general_edit');
		$objTemplate->setData(array(
			'fieldsets' => $this->generateFieldsets('dcbe_general_field', array()),
			'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'versions' => $this->getDC()->getDataProvider()->getVersions($this->getDC()->getId()),
			'language' => $this->objLanguagesSupported,
			'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $this->getDC()->getId() ? 'ID ' . $this->getDC()->getId() : ''),
			'languageHeadline' => strlen($this->strCurrentLanguage) != 0 ? $langsNative[$this->strCurrentLanguage] : '',
			'table' => $this->getDC()->getTable(),
			'enctype' => $this->getDC()->isUploadable() ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
			//'onsubmit' => implode(' ', $this->onsubmit),
			'error' => $this->noReload,
			'buttons' => $this->getDC()->getButtonLabels(),
			'noReload' => $this->getDC()->isNoReload()
		));

		return $objTemplate->parse();
	}

	/**
	 * Show Informations about a data set
	 *
	 * @return String
	 */
	public function show()
	{
		// Load basic informations
		$this->checkLanguage();
		$this->loadCurrentModel();

		// Init
		$fields = array();
		$arrFieldValues = array();
		$arrFieldLabels = array();
		$allowedFields = array('pid', 'sorting', 'tstamp');

		foreach ($this->objCurrentModel as $key => $value)
		{
			$fields[] = $key;
		}

		// Get allowed fieds from dca
		if (is_array($this->getDC()->arrDCA['fields']))
		{
			$allowedFields = array_unique(array_merge($allowedFields, array_keys($this->getDC()->arrDCA['fields'])));
		}

		$fields = array_intersect($allowedFields, $fields);

		// Show all allowed fields
		foreach ($fields as $strFieldName)
		{
			$arrFieldConfig = $this->getDC()->arrDCA['fields'][$strFieldName];

			if (!in_array($strFieldName, $allowedFields)
					|| $arrFieldConfig['inputType'] == 'password'
					|| $arrFieldConfig['eval']['doNotShow']
					|| $arrFieldConfig['eval']['hideInput'])
			{
				continue;
			}

			// Special treatment for table tl_undo
			if ($this->getDC()->getTable() == 'tl_undo' && $strFieldName == 'data')
			{
				continue;
			}

			// Make it human readable
			$arrFieldValues[$strFieldName] = $this->getDC()->getReadableFieldValue($strFieldName, deserialize($this->objCurrentModel->getProperty($strFieldName)));

			// Label
			if (count($arrFieldConfig['label']))
			{
				$arrFieldLabels[$strFieldName] = is_array($arrFieldConfig['label']) ? $arrFieldConfig['label'][0] : $arrFieldConfig['label'];
			}
			else
			{
				$arrFieldLabels[$strFieldName] = is_array($GLOBALS['TL_LANG']['MSC'][$strFieldName]) ? $GLOBALS['TL_LANG']['MSC'][$strFieldName][0] : $GLOBALS['TL_LANG']['MSC'][$strFieldName];
			}

			if (!strlen($arrFieldLabels[$strFieldName]))
			{
				$arrFieldLabels[$strFieldName] = $strFieldName;
			}
		}

		// Create new template
		$objTemplate = new BackendTemplate("dcbe_general_show");
		$objTemplate->headline = sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($this->getDC()->getId() ? 'ID ' . $this->getDC()->getId() : ''));
		$objTemplate->arrFields = $arrFieldValues;
		$objTemplate->arrLabels = $arrFieldLabels;
		$objTemplate->language = $this->objLanguagesSupported;

		return $objTemplate->parse();
	}

	/**
	 * Show all entries from one table
	 * 
	 * @return string HTML
	 */
	public function showAll()
	{
		// Load basic information
		$this->loadCurrentModel();

		// Create return value
		$arrReturn = array();

		// Panels
		switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
		{
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$arrReturn['panel'] = $this->panel();
		}

		// Header buttons
		$arrReturn['buttons'] = $this->generateHeaderButtons($this->getDC()->getButtonId()) . $strReturn;

		// Main body
		switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
		{
			case 0:
			case 1:
			case 2:
			case 3:
				$arrReturn['body'] = $this->viewList();
				break;

			case 4:
				$arrReturn['body'] = $this->viewParent();
				break;

			case 5:
			case 6:
				$arrReturn['body'] = $this->viewTree($this->getDC()->arrDCA['list']['sorting']['mode']);
				break;

			default:
				return $this->notImplMsg;
				break;
		}

		// Return all
		return implode("\n", $arrReturn);
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * AJAX Calls
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	public function ajaxTreeView($intID, $intLevel)
	{
		// Init some Vars
		switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
		{
			case 5:
				$treeClass = 'tree';
				break;

			case 6:
				$treeClass = 'tree_xtnd';
				break;
		}

		$strHTML = $this->generateTreeView($this->getDC()->getCurrentCollecion(), $this->getDC()->arrDCA['list']['sorting']['mode'], $treeClass);

		// Return :P
		return $strHTML;
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Sub Views
	 * Helper functions for the main views
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Parent View + Helper functions
	 * Mode 4
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Show parent view mode 4. 
	 * 
	 * @return string HTML output
	 */
	protected function viewParent()
	{
		// Skip if we have no parent or parent collection.
		if (is_null($this->getDC()->getDataProvider('parent')) || $this->getDC()->getCurrentParentCollection()->length() == 0)
		{
			$this->log('The view for ' . $this->getDC()->getTable() . 'has either a empty parent dataprovider or collection.', __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Load language file and data container array of the parent table
		$this->loadLanguageFile($this->getDC()->getParentTable());
		$this->loadDataContainer($this->getDC()->getParentTable());

		// Get parent DC Driver
		$objParentDC = new DC_General($this->getDC()->getParentTable());
		$this->parentDca = $objParentDC->getDCA();

		// Add template
		$objTemplate = new BackendTemplate('dcbe_general_parentView');

		$objTemplate->collection = $this->getDC()->getCurrentCollecion();
		$objTemplate->select = $this->getDC()->isSelectSubmit();
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->mode = $this->getDC()->arrDCA['list']['sorting']['mode'];
		$objTemplate->table = $this->getDC()->getTable();
		$objTemplate->tableHead = $this->parentView['headerGroup'];
		$objTemplate->header = $this->renderViewParentFormattedHeaderFields();
		$objTemplate->hasSorting = ($this->getDC()->getFirstSorting() == 'sorting');

		// Get dataprovider from current and parent
		$strCDP = $this->getDC()->getDataProvider('self')->getEmptyModel()->getProviderName();
		$strPDP = $this->getDC()->getDataProvider('parent');

		// Add parent provider if exsists
		if ($strPDP != null)
		{
			$strPDP = $strPDP->getEmptyModel()->getProviderName();
		}
		else
		{
			$strPDP = '';
		}

		$objTemplate->pdp = $strPDP;
		$objTemplate->cdp = $strCDP;

		$this->renderViewParentEntries();

		$objTemplate->editHeader = array(
			'content' => $this->generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['editheader'][0]),
			'href' => preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->getDC()->getParentTable()) ? '&amp;table=' . $this->getDC()->getParentTable() : ''), $this->addToUrl('act=edit')),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['editheader'][1])
		);

		$objTemplate->pasteNew = array(
			'content' => $this->generateImage('new.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0]),
			'href' => $this->addToUrl('act=create&amp;mode=2&amp;pid=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID() . '&amp;id=' . $this->intId),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pastenew'][0])
		);

		$objTemplate->pasteAfter = array(
			'content' => $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"'),
			'href' => $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID() . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0])
		);

		$objTemplate->notDeletable = $this->getDC()->arrDCA['config']['notDeletable'];
		$objTemplate->notEditable = $this->getDC()->arrDCA['config']['notEditable'];
		$objTemplate->notEditableParent = $this->parentDca['config']['notEditable'];

		return $objTemplate->parse();
	}

	/* ---------------------------------------------------------------------
	 * parentView helper functions
	 * ------------------------------------------------------------------ */

	/**
	 * Render the entries for parent view. 
	 */
	protected function renderViewParentEntries()
	{
		$strGroup = '';

		// Run each model
		for ($i = 0; $i < $this->getDC()->getCurrentCollecion()->length(); $i++)
		{
			// Get model
			$objModel = $this->getDC()->getCurrentCollecion()->get($i);

			// Set in DC as current for callback and co.
			$this->getDC()->setCurrentModel($objModel);

			// TODO set global current
//                $this->current[] = $objModel->getID();
			// Decrypt encrypted value
			foreach ($objModel as $k => $v)
			{
				if ($this->getDC()->arrDCA['fields'][$k]['eval']['encrypt'])
				{
					$v = deserialize($v);

					$this->import('Encryption');
					$objModel->setProperty($k, $this->Encryption->decrypt($v));
				}
			}

			// Add the group header
			if (!$this->getDC()->arrDCA['list']['sorting']['disableGrouping'] && $this->getDC()->getFirstSorting() != 'sorting')
			{
				// get a list with all fields for sorting
				$orderBy = $this->getDC()->arrDCA['list']['sorting']['fields'];

				// Default ASC
				if (count($orderBy) == 0)
				{
					$sortingMode = 9;
				}
				// If the current First sorting is the default one use the global flag
				else if ($this->getDC()->getFirstSorting() == $orderBy[0])
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
				}
				// Use the field flag, if given
				else if ($this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'] != '')
				{
					$sortingMode = $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'];
				}
				// Use the global as fallback
				else
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
				}

				$remoteNew = $this->getDC()->formatCurrentValue($this->getDC()->getFirstSorting(), $objModel->getProperty($this->getDC()->getFirstSorting()), $sortingMode);
				$group = $this->getDC()->formatGroupHeader($this->getDC()->getFirstSorting(), $remoteNew, $sortingMode, $objModel);

				if ($group != $strGroup)
				{
					$strGroup = $group;
					$objModel->setMeta(DCGE::MODEL_GROUP_HEADER, $group);
				}
			}

			$objModel->setMeta(DCGE::MODEL_CLASS, ($this->getDC()->arrDCA['list']['sorting']['child_record_class'] != '') ? ' ' . $this->getDC()->arrDCA['list']['sorting']['child_record_class'] : '');

			// Regular buttons
			if (!$this->getDC()->isSelectSubmit())
			{
				$strPrevious = ((!is_null($this->getDC()->getCurrentCollecion()->get($i - 1))) ? $this->getDC()->getCurrentCollecion()->get($i - 1)->getID() : null);
				$strNext = ((!is_null($this->getDC()->getCurrentCollecion()->get($i + 1))) ? $this->getDC()->getCurrentCollecion()->get($i + 1)->getID() : null);

				$buttons = $this->generateButtons($objModel, $this->getDC()->getTable(), $this->getDC()->getRootIds(), false, null, $strPrevious, $strNext);

				// Sortable table
				if ($this->parentView['sorting'])
				{
					$buttons .= $this->renderViewParentButtons($objModel);
				}

				$objModel->setMeta(DCGE::MODEL_BUTTONS, $buttons);
			}

			$objModel->setMeta(DCGE::MODEL_LABEL_VALUE, $this->getDC()->getCallbackClass()->childRecordCallback($objModel));
		}
	}

	/**
	 * Render the herader of the parent view with information
	 * from the parent table
	 * 
	 * @return array
	 */
	protected function renderViewParentFormattedHeaderFields()
	{
		$add = array();
		$headerFields = $this->getDC()->arrDCA['list']['sorting']['headerFields'];

		foreach ($headerFields as $v)
		{
			$_v = deserialize($this->getDC()->getCurrentParentCollection()->get(0)->getProperty($v));

			if ($v != 'tstamp' || !isset($this->parentDca['fields'][$v]['foreignKey']))
			{
				if (is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif ($this->parentDca['fields'][$v]['inputType'] == 'checkbox' && !$this->parentDca['fields'][$v]['eval']['multiple'])
				{
					$_v = strlen($_v) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
				}
				elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'date')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $_v);
				}
				elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'time')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $_v);
				}
				elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'datim')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
				}
				elseif (is_array($this->parentDca['fields'][$v]['reference'][$_v]))
				{
					$_v = $this->parentDca['fields'][$v]['reference'][$_v][0];
				}
				elseif (isset($this->parentDca['fields'][$v]['reference'][$_v]))
				{
					$_v = $this->parentDca['fields'][$v]['reference'][$_v];
				}
				elseif ($this->parentDca['fields'][$v]['eval']['isAssociative'] || array_is_assoc($this->parentDca['fields'][$v]['options']))
				{
					$_v = $this->parentDca['fields'][$v]['options'][$_v];
				}
			}

			if ($v == 'tstamp')
			{
				$_v = date($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
			}

			// Add the sorting field
			if ($_v != '')
			{
				$key = isset($GLOBALS['TL_LANG'][$this->getDC()->getParentTable()][$v][0]) ? $GLOBALS['TL_LANG'][$this->getDC()->getParentTable()][$v][0] : $v;
				$add[$key] = $_v;
			}
		}

		// Trigger the header_callback
		$arrHeaderCallback = $this->getDC()->getCallbackClass()->headerCallback($add);

		if (!is_null($arrHeaderCallback))
		{
			$add = $arrHeaderCallback;
		}

		// Set header data
		$arrHeader = array();
		foreach ($add as $k => $v)
		{
			if (is_array($v))
			{
				$v = $v[0];
			}

			$arrHeader[$k] = $v;
		}

		return $arrHeader;
	}

	/**
	 * @todo Update for clipboard
	 * @param InterfaceGeneralModel $objModel
	 * @return string
	 */
	protected function renderViewParentButtons($objModel)
	{
		$arrReturn = array();
		$blnClipboard = $blnMultiboard = false;

		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][1], $objModel->getID()), 'class="blink"');
		$imagePasteNew = $this->generateImage('new.gif', sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pastenew'][1], $objModel->getID()));

		// Create new button
		if (!$this->getDC()->arrDCA['config']['closed'])
		{
			$arrReturn[] = ' <a href="' . $this->addToUrl('act=create&amp;mode=1&amp;pid=' . $objModel->getID() . '&amp;id=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID()) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pastenew'][1], $row[$i]['id'])) . '">' . $imagePasteNew . '</a>';
		}

		// TODO clipboard
		// Prevent circular references
		if ($blnClipboard && $arrClipboard['mode'] == 'cut' && $objModel->getID() == $arrClipboard['id'] || $blnMultiboard && $arrClipboard['mode'] == 'cutAll' && in_array($row[$i]['id'], $arrClipboard['id']))
		{
			$arrReturn[] = ' ' . $this->generateImage('pasteafter_.gif', '', 'class="blink"');
		}

		// TODO clipboard
		// Copy/move multiple
		elseif ($blnMultiboard)
		{
			$arrReturn[] = ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
		}

		// TODO clipboard
		// Paste buttons
		elseif ($blnClipboard)
		{
			$arrReturn[] = ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id'] . '&amp;id=' . $arrClipboard['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
		}

		return implode('', $arrReturn);
	}

	//----------------------------------------------------------------------

	/**
	 * Generate list view from current collection
	 *
	 * @return string
	 */
	protected function viewList()
	{
		// Set label
		$this->setListViewLabel();

		// Generate buttons
		foreach ($this->getDC()->getCurrentCollecion() as $objModelRow)
		{
			$objModelRow->setMeta(DCGE::MODEL_BUTTONS, $this->generateButtons($objModelRow, $this->getDC()->getTable(), $this->getDC()->getRootIds()));
		}

		// Add template
		if ($this->getDC()->getFirstSorting() == 'sorting')
		{
			$objTemplate = new BackendTemplate('dcbe_general_listView_sorting');
		}
		else
		{
			$objTemplate = new BackendTemplate('dcbe_general_listView');
		}
		
		$objTemplate->collection = $this->getDC()->getCurrentCollecion();
		$objTemplate->select = $this->getDC()->isSelectSubmit();
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->mode = $this->getDC()->arrDCA['list']['sorting']['mode'];
		$objTemplate->tableHead = $this->getTableHead();
		$objTemplate->notDeletable = $this->getDC()->arrDCA['config']['notDeletable'];
		$objTemplate->notEditable = $this->getDC()->arrDCA['config']['notEditable'];

		// Set dataprovider from current and parent
		$objTemplate->pdp = '';
		$objTemplate->cdp = $this->getDC()->getDataProvider('self')->getEmptyModel()->getProviderName();
		
		return $objTemplate->parse();
	}

	protected function viewTree($intMode = 5)
	{
		// Init some Vars
		switch ($intMode)
		{
			case 5:
				$treeClass = 'tree';
				break;

			case 6:
				$treeClass = 'tree_xtnd';
				break;
		}

		// Label + Icon
		$strLabelText = (strlen($this->getDC()->arrDCA['config']['label']) == 0 ) ? 'DC General Tree View Ultimate' : $this->getDC()->arrDCA['config']['label'];
		$strLabelIcon = strlen($this->getDC()->arrDCA['list']['sorting']['icon']) ? $this->getDC()->arrDCA['list']['sorting']['icon'] : 'pagemounts.gif';

		// Rootpage pasteinto
		if ($this->getDC()->isClipboard())
		{
			$arrClipboard = $this->getDC()->getClipboard();
			// TODO: @CS we definately need into and after handling here instead of different modes.
			$imagePasteInto = $this->generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0], 'class="blink"');
			$strRootPasteinto = '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;after=0&amp;pid=0&amp;id=' . $arrClipboard['id'] . '&amp;childs=' . $arrClipboard['childs']) . '" title="' . specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0]) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto . '</a> ';
		}

		// Create treeview
		$strHTML = $this->generateTreeView($this->getDC()->getCurrentCollecion(), $intMode, $treeClass);

		// Build template
		$objTemplate = new BackendTemplate('dcbe_general_treeview');
		$objTemplate->treeClass = 'tl_' . $treeClass;
		$objTemplate->strLabelIcon = $this->generateImage($strLabelIcon);
		$objTemplate->strLabelText = $strLabelText;
		$objTemplate->strHTML = $strHTML;
		$objTemplate->intMode = $intMode;
		$objTemplate->strRootPasteinto = $strRootPasteinto;

		// Return :P
		return $objTemplate->parse();
	}

	protected function generateTreeView($objCollection, $intMode, $treeClass)
	{
		$strHTML = '';

		foreach ($objCollection as $objModel)
		{
			$objModel->setMeta(DCGE::MODEL_BUTTONS, $this->generateButtons($objModel, $this->getDC()->getTable()));

			$strToggleID = $this->getDC()->getTable() . '_' . $treeClass . '_' . $objModel->getID();

			$objEntryTemplate = new BackendTemplate('dcbe_general_treeview_entry');
			$objEntryTemplate->objModel = $objModel;
			$objEntryTemplate->intMode = $intMode;
			$objEntryTemplate->strToggleID = $strToggleID;

			$strHTML .= $objEntryTemplate->parse();
			$strHTML .= "\n";

			if ($objModel->getMeta(DCGE::TREE_VIEW_HAS_CHILDS) == true && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) == true)
			{
				$objChildTemplate = new BackendTemplate('dcbe_general_treeview_child');
				$objChildTemplate->objParentModel = $objModel;
				$objChildTemplate->strToggleID = $strToggleID;
				$strSubHTML = '';
				foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
				{
					$strSubHTML .= $this->generateTreeView($objCollection, $intMode, $treeClass);
				}
				$objChildTemplate->strHTML = $strSubHTML;
				$objChildTemplate->strTable = $this->getDC()->getTable();

				$strHTML .= $objChildTemplate->parse();
				$strHTML .= "\n";
			}
		}

		return $strHTML;
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Palette Helper Functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Get all selectors from dca
	 *
	 * @param array $arrSubpalettes
	 * @return void
	 */
	protected function calculateSelectors(array $arrSubpalettes = null)
	{
		// Check if we have a array
		if (!is_array($arrSubpalettes))
		{
			return;
		}

		foreach ($arrSubpalettes as $strField => $varSubpalette)
		{
			$this->arrSelectors[$strField] = $this->getDC()->isEditableField($strField);

			if (!is_array($varSubpalette))
			{
				continue;
			}

			foreach ($varSubpalette as $arrNested)
			{
				if (is_array($arrNested))
				{
					$this->calculateSelectors($arrNested['subpalettes']);
				}
			}
		}
	}

	protected function parseRootPalette()
	{
		foreach (trimsplit(';', $this->selectRootPalette()) as $strPalette)
		{
			if ($strPalette[0] == '{')
			{
				list($strLegend, $strPalette) = explode(',', $strPalette, 2);
			}

			$arrPalette = $this->parsePalette($strPalette, array());

			if ($arrPalette)
			{
				$this->arrRootPalette[] = array(
					'legend' => $strLegend,
					'palette' => $arrPalette
				);
			}
		}

		// Callback
		$this->arrRootPalette = $this->getDC()->getCallbackClass()->parseRootPaletteCallback($this->arrRootPalette);
	}

	protected function selectRootPalette()
	{
		$arrPalettes = $this->getDC()->getPalettesDefinition();
		$arrSelectors = $arrPalettes['__selector__'];

		if (!is_array($arrSelectors))
		{
			return $arrPalettes['default'];
		}

		$arrKeys = array();

		foreach ($arrSelectors as $strSelector)
		{
			$varValue = $this->objCurrentModel->getProperty($strSelector);
			if (!strlen($varValue))
			{
				continue;
			}

			$arrDef = $this->getDC()->getFieldDefinition($strSelector);
			$arrKeys[] = ($arrDef['inputType'] == 'checkbox' && !$arrDef['eval']['multiple']) ? $strSelector : $varValue;
		}

		// Build possible palette names from the selector values
		if (!$arrKeys)
		{
			return $arrPalettes['default'];
		}

		// Get an existing palette
		foreach (self::combiner($arrKeys) as $strKey)
		{
			if (is_string($arrPalettes[$strKey]))
			{
				return $arrPalettes[$strKey];
			}
		}

		// ToDo: ??? why exit on this place

		return $arrPalettes['default'];
	}

	protected function parsePalette($strPalette, array $arrPalette)
	{
		if (!$strPalette)
		{
			return $arrPalette;
		}

		foreach (trimsplit(',', $strPalette) as $strField)
		{
			if (!$strField)
			{
				continue;
			}

			$varValue = $this->objCurrentModel->getProperty($strField);
			$varSubpalette = $this->getSubpalette($strField, $varValue);

			if (is_array($varSubpalette))
			{
				$arrSubpalettes = $varSubpalette['subpalettes'];
				$varSubpalette = $varSubpalette['palette'];
			}

			array_push($this->arrStack, is_array($arrSubpalettes) ? $arrSubpalettes : array());

			if ($this->getDC()->isEditableField($strField))
			{
				$arrPalette[] = $strField;
				$arrSubpalette = $this->parsePalette($varSubpalette, array());
				if ($arrSubpalette)
				{
					$arrPalette[] = $arrSubpalette;
					if ($this->isSelector($strField))
					{
						$this->arrAjaxPalettes[$strField] = $arrSubpalette;
					}
				}
			}
			else
			{ // selector field not editable, inline editable fields of active subpalette
				$arrPalette = $this->parsePalette($varSubpalette, $arrPalette);
			}

			array_pop($this->arrStack);
		}

		return $arrPalette;
	}

	protected function getSubpalette($strField, $varValue)
	{
		if ($this->arrAjaxPalettes[$strField])
		{
			throw new Exception("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
		}

		for ($i = count($this->arrStack) - 1; $i > -1; $i--)
		{
			if (isset($this->arrStack[$i][$strField]))
			{
				if (is_array($this->arrStack[$i][$strField]))
				{
					return $this->arrStack[$i][$strField][$varValue];
				}
				else
				{ // old style
					return $varValue ? $this->arrStack[$i][$strField] : null;
				}
			}
			elseif (isset($this->arrStack[$i][$strField . '_' . $varValue]))
			{
				return $this->arrStack[$i][$strField . '_' . $varValue];
			}
		}
	}

	public function generateFieldsets($strFieldTemplate)
	{
		// Load the states of legends
		$arrFieldsetStates = $this->Session->get('fieldset_states');
		$arrFieldsetStates = $arrFieldsetStates[$this->getDC()->getTable()];
		if (!is_array($arrFieldsetStates))
		{
			$arrFieldsetStates = array();
		}

		$arrRootPalette = $this->arrRootPalette;



		foreach ($arrRootPalette as &$arrFieldset)
		{
			$strClass = 'tl_box';

			if ($strLegend = &$arrFieldset['legend'])
			{
				$arrClasses = explode(':', substr($strLegend, 1, -1));
				$strLegend = array_shift($arrClasses);
				$arrClasses = array_flip($arrClasses);
				if (isset($arrFieldsetStates[$strLegend]))
				{
					if ($arrFieldsetStates[$strLegend])
					{
						unset($arrClasses['hide']);
					}
					else
					{
						$arrClasses['collapsed'] = true;
					}
				}
				$strClass .= ' ' . implode(' ', array_keys($arrClasses));
				$arrFieldset['label'] = isset($GLOBALS['TL_LANG'][$this->getDC()->getTable()][$strLegend]) ? $GLOBALS['TL_LANG'][$this->getDC()->getTable()][$strLegend] : $strLegend;
			}

			$arrFieldset['class'] = $strClass;
			$arrFieldset['palette'] = $this->generatePalette($arrFieldset['palette'], $strFieldTemplate);
		}

		return $arrRootPalette;
	}

	/**
	 * Generates a subpalette for the given selector (field name)
	 *
	 * @param string $strSelector the name of the selector field.
	 *
	 * @return string the generated HTML code.
	 */
	public function generateAjaxPalette($strSelector)
	{
		// Load basic informations
		$this->checkLanguage();
		$this->loadCurrentModel();

		// Get all selectors
		$this->arrStack[] = $this->getDC()->getSubpalettesDefinition();
		$this->calculateSelectors($this->arrStack[0]);
		$this->parseRootPalette();

		return is_array($this->arrAjaxPalettes[$strSelector]) ? $this->generatePalette($this->arrAjaxPalettes[$strSelector], 'dcbe_general_field') : '';
	}

	protected function generatePalette(array $arrPalette, $strFieldTemplate)
	{
		ob_start();

		foreach ($arrPalette as $varField)
		{
			if (is_array($varField))
			{
				/* $strName => this is the input name from the last loop */
				echo '<div id="sub_' . $strName . '">', $this->generatePalette($varField, $strFieldTemplate), '</div>';
			}
			else
			{
				$objWidget = $this->getDC()->getWidget($varField);

				if (!$objWidget instanceof Widget)
				{
					echo $objWidget;
					continue;
				}

				$arrConfig = $this->getDC()->getFieldDefinition($varField);

				$strClass = $arrConfig['eval']['tl_class'];

				// TODO: this should be correctly specified in DCAs
//				if($arrConfig['inputType'] == 'checkbox'
//				&& !$arrConfig['eval']['multiple']
//				&& strpos($strClass, 'w50') !== false
//				&& strpos($strClass, 'cbx') === false)
//					$strClass .= ' cbx';

				$strName = specialchars($objWidget->name);
				$blnUpdate = $arrConfig['update'];
				$strDatepicker = '';

				if ($arrConfig['eval']['datepicker'])
				{
					if (version_compare(VERSION, '2.10', '>='))
					{
						$strDatepicker = $this->buildDatePicker($objWidget);
					}
					else
					{
						$strDatepicker = sprintf($arrConfig['eval']['datepicker'], json_encode('ctrl_' . $objWidget->id));
					}
				}

				// TODO: Maybe TemplateFoo is not such a good name :?
				$objTemplateFoo = new BackendTemplate($strFieldTemplate);
				$objTemplateFoo->strName = $strName;
				$objTemplateFoo->strClass = $strClass;
				$objTemplateFoo->objWidget = $objWidget;
				$objTemplateFoo->strDatepicker = $strDatepicker;
				$objTemplateFoo->blnUpdate = $blnUpdate;
				$objTemplateFoo->strHelp = $this->getDC()->generateHelpText($varField);

				echo $objTemplateFoo->parse();

				if (strncmp($arrConfig['eval']['rte'], 'tiny', 4) === 0 && (version_compare(VERSION, '2.10', '>=') || $this->Input->post('isAjax')))
				{
					echo '<script>tinyMCE.execCommand("mceAddControl", false, "ctrl_' . $strName . '");</script>';
				}
			}
		}

		return ob_get_clean();
	}

	protected function buildDatePicker($objWidget)
	{
		$strFormat = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

		$arrConfig = array(
			'allowEmpty' => true,
			'toggleElements' => '#toggle_' . $objWidget->id,
			'pickerClass' => 'datepicker_dashboard',
			'format' => $strFormat,
			'inputOutputFormat' => $strFormat,
			'positionOffset' => array(
				'x' => 130,
				'y' => -185
			),
			'startDay' => $GLOBALS['TL_LANG']['MSC']['weekOffset'],
			'days' => array_values($GLOBALS['TL_LANG']['DAYS']),
			'dayShort' => $GLOBALS['TL_LANG']['MSC']['dayShortLength'],
			'months' => array_values($GLOBALS['TL_LANG']['MONTHS']),
			'monthShort' => $GLOBALS['TL_LANG']['MSC']['monthShortLength']
		);

		switch ($objWidget->rgxp)
		{
			case 'datim':
				$arrConfig['timePicker'] = true;
				$time = ",\n      timePicker:true";
				break;

			case 'time':
				$arrConfig['timePickerOnly'] = true;
				$time = ",\n      pickOnly:\"time\"";
				break;
			default:
				$time = '';
		}

		if (version_compare(DATEPICKER, '2.1','>')) return 'new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
			draggable:false,
			toggle:$$("#toggle_' . $objWidget->id . '"),
			format:"' . Date::formatToJs($strFormat) . '",
			positionOffset:{x:-197,y:-182}' . $time . ',
			pickerClass:"datepicker_dashboard",
			useFadeInOut:!Browser.ie,
			startDay:' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
			titleFormat:"' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
		});';
		return 'new DatePicker(' . json_encode('#ctrl_' . $objWidget->id) . ', ' . json_encode($arrConfig) . ');';
	}

	public static function combiner($names)
	{
		$return = array('');

		for ($i = 0; $i < count($names); $i++)
		{
			$buffer = array();

			foreach ($return as $k => $v)
			{
				$buffer[] = ($k % 2 == 0) ? $v : $v . $names[$i];
				$buffer[] = ($k % 2 == 0) ? $v . $names[$i] : $v;
			}

			$return = $buffer;
		}

		return array_filter($return);
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * listView helper functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	protected function getTableHead()
	{
		$arrTableHead = array();

		// Generate the table header if the "show columns" option is active
		if ($this->getDC()->arrDCA['list']['label']['showColumns'])
		{
			foreach ($this->getDC()->arrDCA['list']['label']['fields'] as $f)
			{
				$arrTableHead[] = array(
					'class' => 'tl_folder_tlist col_' . $f . (($f == $this->getDC()->getFirstSorting()) ? ' ordered_by' : ''),
					'content' => $this->getDC()->arrDCA['fields'][$f]['label'][0]
				);
			}

			$arrTableHead[] = array(
				'class' => 'tl_folder_tlist tl_right_nowrap',
				'content' => '&nbsp;'
			);
		}

		return $arrTableHead;
	}

	/**
	 * Set label for list view
	 */
	protected function setListViewLabel()
	{
		// Automatically add the "order by" field as last column if we do not have group headers
		if ($this->getDC()->arrDCA['list']['label']['showColumns'] && !in_array($this->getDC()->getFirstSorting(), $this->getDC()->arrDCA['list']['label']['fields']))
		{
			$this->getDC()->arrDCA['list']['label']['fields'][] = $this->getDC()->getFirstSorting();
		}

		$remoteCur = false;
		$groupclass = 'tl_folder_tlist';
		$eoCount = -1;

		foreach ($this->getDC()->getCurrentCollecion() as $objModelRow)
		{
			$args = $this->getListViewLabelArguments($objModelRow);

			// Shorten the label if it is too long
			$label = vsprintf((strlen($this->getDC()->arrDCA['list']['label']['format']) ? $this->getDC()->arrDCA['list']['label']['format'] : '%s'), $args);

			if ($this->getDC()->arrDCA['list']['label']['maxCharacters'] > 0 && $this->getDC()->arrDCA['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
			{
				$this->import('String');
				$label = trim($this->String->substrHtml($label, $this->getDC()->arrDCA['list']['label']['maxCharacters'])) . ' â€¦';
			}

			// Remove empty brackets (), [], {}, <> and empty tags from the label
			$label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
			$label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

			// Build the sorting groups
			if ($this->getDC()->arrDCA['list']['sorting']['mode'] > 0)
			{

				// Get the current value of first sorting
				$current = $objModelRow->getProperty($this->getDC()->getFirstSorting());
				$orderBy = $this->getDC()->arrDCA['list']['sorting']['fields'];

				// Default ASC
				if (count($orderBy) == 0)
				{
					$sortingMode = 9;
				}
				// If the current First sorting is the default one use the global flag
				else if ($this->getDC()->getFirstSorting() == $orderBy[0])
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
				}
				// Use the fild flag, if given
				else if ($this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'] != '')
				{
					$sortingMode = $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'];
				}
				// Use the global as fallback
				else
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
				}

				// ToDo: Why such a big if ?
//				$sortingMode = (count($orderBy) == 1 && $this->getDC()->getFirstSorting() == $orderBy[0] && $this->getDC()->arrDCA['list']['sorting']['flag'] != '' && $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'] == '') ? $this->getDC()->arrDCA['list']['sorting']['flag'] : $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'];

				$remoteNew = $this->getDC()->formatCurrentValue($this->getDC()->getFirstSorting(), $current, $sortingMode);

				// Add the group header
				if (!$this->getDC()->arrDCA['list']['label']['showColumns'] && !$this->getDC()->arrDCA['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
				{
					$eoCount = -1;

					$objModelRow->setMeta(DCGE::MODEL_GROUP_VALUE, array(
						'class' => $groupclass,
						'value' => $this->getDC()->formatGroupHeader($this->getDC()->getFirstSorting(), $remoteNew, $sortingMode, $objModelRow)
					));

					$groupclass = 'tl_folder_list';
					$remoteCur = $remoteNew;
				}
			}

			$objModelRow->setMeta(DCGE::MODEL_EVEN_ODD_CLASS, ((++$eoCount % 2 == 0) ? 'even' : 'odd'));

			$colspan = 1;

			// Call label callback
			$mixedArgs = $this->getDC()->getCallbackClass()->labelCallback($objModelRow, $label, $this->getDC()->arrDCA['list']['label'], $args);

			if (!is_null($mixedArgs))
			{
				// Handle strings and arrays (backwards compatibility)
				if (!$this->getDC()->arrDCA['list']['label']['showColumns'])
				{
					$label = is_array($mixedArgs) ? implode(' ', $mixedArgs) : $mixedArgs;
				}
				elseif (!is_array($mixedArgs))
				{
					$mixedArgs = array($mixedArgs);
					$colspan = count($this->getDC()->arrDCA['list']['label']['fields']);
				}
			}

			$arrLabel = array();

			// Add columns
			if ($this->getDC()->arrDCA['list']['label']['showColumns'])
			{
				foreach ($args as $j => $arg)
				{
					$arrLabel[] = array(
						'colspan' => $colspan,
						'class' => 'tl_file_list col_' . $this->getDC()->arrDCA['list']['label']['fields'][$j] . (($this->getDC()->arrDCA['list']['label']['fields'][$j] == $this->getDC()->getFirstSorting()) ? ' ordered_by' : ''),
						'content' => (($arg != '') ? $arg : '-')
					);
				}
			}
			else
			{
				$arrLabel[] = array(
					'colspan' => NULL,
					'class' => 'tl_file_list',
					'content' => $label
				);
			}

			$objModelRow->setMeta(DCGE::MODEL_LABEL_VALUE, $arrLabel);
		}
	}

	/**
	 * Get arguments for label
	 *
	 * @param InterfaceGeneralModel $objModelRow
	 * @return array
	 */
	protected function getListViewLabelArguments($objModelRow)
	{
		if ($this->getDC()->arrDCA['list']['sorting']['mode'] == 6)
		{
			$this->loadDataContainer($objDC->getParentTable());
			$objTmpDC = new DC_General($objDC->getParentTable());

			$arrCurrentDCA = $objTmpDC->getDCA();
		}
		else
		{
			$arrCurrentDCA = $this->getDC()->arrDCA;
		}

		$args = array();
		$showFields = $arrCurrentDCA['list']['label']['fields'];

		// Label
		foreach ($showFields as $k => $v)
		{
			if (strpos($v, ':') !== false)
			{
				$args[$k] = $objModelRow->getMeta(DCGE::MODEL_LABEL_ARGS);
			}
			elseif (in_array($this->getDC()->arrDCA['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
			{
				if ($this->getDC()->arrDCA['fields'][$v]['eval']['rgxp'] == 'date')
				{
					$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objModelRow->getProperty($v));
				}
				elseif ($this->getDC()->arrDCA['fields'][$v]['eval']['rgxp'] == 'time')
				{
					$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objModelRow->getProperty($v));
				}
				else
				{
					$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objModelRow->getProperty($v));
				}
			}
			elseif ($this->getDC()->arrDCA['fields'][$v]['inputType'] == 'checkbox' && !$this->getDC()->arrDCA['fields'][$v]['eval']['multiple'])
			{
				$args[$k] = strlen($objModelRow->getProperty($v)) ? $arrCurrentDCA['fields'][$v]['label'][0] : '';
			}
			else
			{
				$row = deserialize($objModelRow->getProperty($v));

				if (is_array($row))
				{
					$args_k = array();

					foreach ($row as $option)
					{
						$args_k[] = strlen($arrCurrentDCA['fields'][$v]['reference'][$option]) ? $arrCurrentDCA['fields'][$v]['reference'][$option] : $option;
					}

					$args[$k] = implode(', ', $args_k);
				}
				elseif (isset($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]))
				{
					$args[$k] = is_array($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]) ? $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)][0] : $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)];
				}
				elseif (($arrCurrentDCA['fields'][$v]['eval']['isAssociative'] || array_is_assoc($arrCurrentDCA['fields'][$v]['options'])) && isset($arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)]))
				{
					$args[$k] = $arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)];
				}
				else
				{
					$args[$k] = $objModelRow->getProperty($v);
				}
			}
		}

		return $args;
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Button functions
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Generate all button for the header of a view.
	 * 
	 * This is a function, which is a combination of 
	 * displayButtons and generateGlobalButtons.
	 */
	protected function generateHeaderButtons($strButtonId)
	{
		// Return array with all button		
		$arrReturn = array();

		// Add back button
		if ($this->getDC()->isSelectSubmit() || $this->getDC()->getParentTable())
		{
			$arrReturn['back_button'] = sprintf('<a href="%s" class="header_back" title="%s" accesskey="b" onclick="Backend.getScrollOffset();">%s</a>', $this->getReferer(true, $this->getDC()->getParentTable()), specialchars($GLOBALS['TL_LANG']['MSC']['backBT']), $GLOBALS['TL_LANG']['MSC']['backBT']);
		}

		// Check if we have the select mode
		if (!$this->getDC()->isSelectSubmit())
		{
			// Add Buttons for mode x
			switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
			{
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
					// Add new button
					$strHref = '';
					if (strlen($this->getDC()->getParentTable()))
					{
						if ($this->getDC()->arrDCA['list']['sorting']['mode'] < 4)
						{
							$strHref = '&amp;mode=2';
						}
						$strHref = $this->addToUrl($strHref . '&amp;id=&amp;act=create&amp;pid=' . $this->getDC()->getId());
					}
					else
					{
						$strHref = $this->addToUrl('act=create');
					}

					$arrReturn['button_new'] = (!$this->getDC()->arrDCA['config']['closed'] ? sprintf(' <a href="%s" class="header_new" title="%s" accesskey="n" onclick="Backend.getScrollOffset()">%s</a>', $strHref, specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][1]), $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][0]) : '');
					break;

				case 5:
				case 6:
					// Add new button
					$strCDP = $this->getDC()->getDataProvider('self')->getEmptyModel()->getProviderName();
					
					if ($this->getDC()->getDataProvider('parent') != null)
					{
						$strPDP = $this->getDC()->getDataProvider('parent')->getEmptyModel()->getProviderName();
					}
					else
					{
						$strPDP = null;
					}
					
					if (!($this->getDC()->arrDCA['config']['closed'] || $this->getDC()->isClipboard()))
					{
						$arrReturn['button_new'] = sprintf('<a href="%s" class="header_new" title="%s" accesskey="n" onclick="Backend.getScrollOffset()">%s</a>', $this->addToUrl(sprintf('act=paste&amp;mode=create&amp;cdp=%s&amp;pdp=%s', $strCDP, $strPDP)), specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][1]), $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][0]);
					}

					break;
			}
		}

		// add clear clipboard button if needed.
		if ($this->getDC()->isClipboard())
		{
			$arrReturn['button_clipboard'] = sprintf('<a href="%s" class="header_clipboard" title="%s" accesskey="x">%s</a>', $this->addToUrl('clipboard=1'), specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']), $GLOBALS['TL_LANG']['MSC']['clearClipboard']);
		}

		// Add global buttons
		if (is_array($this->getDC()->arrDCA['list']['global_operations']))
		{
			foreach ($this->getDC()->arrDCA['list']['global_operations'] as $k => $v)
			{
				$v = is_array($v) ? $v : array($v);
				$label = is_array($v['label']) ? $v['label'][0] : $v['label'];
				$title = is_array($v['label']) ? $v['label'][1] : $v['label'];
				$attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

				if (!strlen($label))
				{
					$label = $k;
				}

				// Call a custom function instead of using the default button
				$strButtonCallback = $this->getDC()->getCallbackClass()->globalButtonCallback($v, $label, $title, $attributes, $this->getDC()->getTable(), $this->getDC()->getRootIds());
				if (!is_null($strButtonCallback))
				{
					$arrReturn[$k] = $strButtonCallback;
					continue;
				}

				$arrReturn[$k] = '<a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
			}
		}


		return '<div id="' . $strButtonId . '">' . implode(' &nbsp; :: &nbsp; ', $arrReturn) . '</div>';
	}

	/**
	 * Generate header display buttons
	 *
	 * @param string $strButtonId
	 * @return string
	 */
	protected function displayButtons($strButtonId)
	{
		$arrReturn = array();

		// Add open wrapper
		$arrReturn[] = '<div id="' . $strButtonId . '">';

		// Add back button
		$arrReturn[] = (($this->getDC()->isSelectSubmit() || $this->getDC()->getParentTable()) ? '<a href="' . $this->getReferer(true, $this->getDC()->getParentTable()) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' : '');

		// Add divider
		$arrReturn[] = (($this->getDC()->getParentTable() && !$this->getDC()->isSelectSubmit()) ? ' &nbsp; :: &nbsp;' : '');

		if (!$this->getDC()->isSelectSubmit())
		{
			switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
			{
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
					// Add new button
					$strHref = '';
					if (strlen($this->getDC()->getParentTable()))
					{
						if ($this->getDC()->arrDCA['list']['sorting']['mode'] < 4)
						{
							$strHref = '&amp;mode=2';
						}
						$strHref = $this->addToUrl($strHref . '&amp;id=&amp;act=create&amp;pid=' . $this->getDC()->getId());
					}
					else
					{
						$strHref = $this->addToUrl('act=create');
					}

					$arrReturn[] = (!$this->getDC()->arrDCA['config']['closed'] ?
									sprintf(
											' <a href="%s" class="header_new" title="%s" accesskey="n" onclick="Backend.getScrollOffset()">%s</a>', $strHref, specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][1]), $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][0]
									) : '');
					break;

				case 5:
				case 6:
					// Add new button
					$strCDP = $this->getDC()->getDataProvider('self')->getEmptyModel()->getProviderName();
					$strPDP = $this->getDC()->getDataProvider('parent')->getEmptyModel()->getProviderName();


					$arrReturn[] = (!($this->getDC()->arrDCA['config']['closed'] || $this->getDC()->isClipboard())) ?
							sprintf(
									' <a href="%s" class="header_new" title="%s" accesskey="n" onclick="Backend.getScrollOffset()">%s</a>', $this->addToUrl(sprintf('act=paste&amp;mode=create&amp;cdp=%s&amp;pdp=%s', $strCDP, $strPDP)), specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][1]), $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][0]
							) : '';
					// add clear clipboard button if needed.
					if ($this->getDC()->isClipboard())
					{
						$arrReturn[] = sprintf(
								' <a href="%s" class="header_clipboard" title="%s" accesskey="x">%s</a>', $this->addToUrl('clipboard=1'), specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']), $GLOBALS['TL_LANG']['MSC']['clearClipboard']
						);
					}
					break;
			}

			// Add global buttons
			$arrReturn[] = $this->generateGlobalButtons();
		}

		// Add close wrapper
		$arrReturn[] = '</div>';

		$arrReturn[] = $this->getMessages(true);

		return implode('', $arrReturn);
	}

	/**
	 * Compile global buttons from the table configuration array and return them as HTML
	 *
	 * @param boolean $blnForceSeparator
	 * @return string
	 */
	protected function generateGlobalButtons($blnForceSeparator = false)
	{
		if (!is_array($this->getDC()->arrDCA['list']['global_operations']))
		{
			return '';
		}

		$return = '';

		foreach ($this->getDC()->arrDCA['list']['global_operations'] as $k => $v)
		{
			$v = is_array($v) ? $v : array($v);
			$label = is_array($v['label']) ? $v['label'][0] : $v['label'];
			$title = is_array($v['label']) ? $v['label'][1] : $v['label'];
			$attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

			if (!strlen($label))
			{
				$label = $k;
			}

			// Call a custom function instead of using the default button
			$strButtonCallback = $this->getDC()->getCallbackClass()->globalButtonCallback($v, $label, $title, $attributes, $this->getDC()->getTable(), $this->getDC()->getRootIds());
			if (!is_null($strButtonCallback))
			{
				$return .= $strButtonCallback;
				continue;
			}

			$return .= ' &#160; :: &#160; <a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
		}

		if ($this->getDC()->isClipboard())
		{
			$return .= ' &#160; :: &#160; <a href="' . $this->addToUrl('clipboard=1') . '" class="header_clipboard" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a>';
		}

		return ($this->getDC()->arrDCA['config']['closed'] && !$blnForceSeparator) ? preg_replace('/^ &#160; :: &#160; /', '', $return) : $return;
	}

	/**
	 * Compile buttons from the table configuration array and return them as HTML
	 *
	 * @param InterfaceGeneralModel $objModelRow
	 * @param string $strTable
	 * @param array $arrRootIds
	 * @param boolean $blnCircularReference
	 * @param array $arrChildRecordIds
	 * @param int $strPrevious
	 * @param int $strNext
	 * @return string
	 */
	protected function generateButtons(InterfaceGeneralModel $objModelRow, $strTable, $arrRootIds = array(), $blnCircularReference = false, $arrChildRecordIds = null, $strPrevious = null, $strNext = null)
	{
		if (!count($GLOBALS['TL_DCA'][$strTable]['list']['operations']))
		{
			return '';
		}

		$return = '';

		foreach ($GLOBALS['TL_DCA'][$strTable]['list']['operations'] as $k => $v)
		{
			// Check if we have a array
			if (!is_array($v))
			{
				$v = array($v);
			}

			// Set basic informations
			$label = strlen($v['label'][0]) ? $v['label'][0] : $k;
			$title = sprintf((strlen($v['label'][1]) ? $v['label'][1] : $k), $objModelRow->getID());
			$attributes = strlen($v['attributes']) ? ' ' . ltrim(sprintf($v['attributes'], $objModelRow->getID(), $objModelRow->getID())) : '';

			// Call a custom function instead of using the default button
			$strButtonCallback = $this->getDC()->getCallbackClass()
					->buttonCallback($objModelRow, $v, $label, $title, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);

			if (!is_null($strButtonCallback))
			{
				$return .= ' ' . trim($strButtonCallback);
				continue;
			}

			// Generate all buttons except "move up" and "move down" buttons
			if ($k != 'move' && $v != 'move')
			{
				switch ($k)
				{
					// Cute needs some special informations
					case 'cut':
						// Get dataprovider from current and parent
						$strCDP = $objModelRow->getProviderName();
						$strPDP = $objModelRow->getMeta(DCGE::MODEL_PTABLE);

						$strAdd2Url = "";

						// Add url + id + currentDataProvider
						$strAdd2Url .= $v['href'] . '&amp;cdp=' . $strCDP;

						// Add parent provider if exsists
						if ($strPDP != null)
						{
							$strPDP = $strPDP;
							$strAdd2Url .= '&amp;pdp=' . $strPDP;
						}

						// If we have a id add it, used for mode 4 and all parent -> current views
						if (strlen($this->Input->get('id')) != 0)
						{
							$strAdd2Url .= '&amp;id=' . $this->Input->get('id');
						}

						// Source is the id of the element which should move
						$strAdd2Url .= '&amp;source=' . $objModelRow->getID();

						// Build whole button mark up
						$return .= ' <a href="'
								. $this->addToUrl($strAdd2Url)
								. '" title="' . specialchars($title)
								. '"'
								. $attributes
								. '>'
								. $this->generateImage($v['icon'], $label)
								. '</a>';
						break;

					default:
						$idParam = $v['idparam'] ? 'id=&amp;' . $v['idparam'] : 'id';
						$strUrl = $this->addToUrl($v['href'] . '&amp;' . $idParam . '=' . $objModelRow->getID());
						$return .= ' <a href="'
								. $strUrl
								. '" title="' . specialchars($title)
								. '"'
								. $attributes
								. '>'
								. $this->generateImage($v['icon'], $label)
								. '</a>';
						break;
				}
				continue;
			}

			$arrRootIds = is_array($arrRootIds) ? $arrRootIds : array($arrRootIds);

			foreach (array('up', 'down') as $dir)
			{
				$label = strlen($GLOBALS['TL_LANG'][$strTable][$dir][0]) ? $GLOBALS['TL_LANG'][$strTable][$dir][0] : $dir;
				$title = strlen($GLOBALS['TL_LANG'][$strTable][$dir][1]) ? $GLOBALS['TL_LANG'][$strTable][$dir][1] : $dir;

				$label = $this->generateImage($dir . '.gif', $label);
				$href = strlen($v['href']) ? $v['href'] : '&amp;act=move';

				if ($dir == 'up')
				{
					$return .= ((is_numeric($strPrevious) && (!in_array($objModelRow->getID(), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? ' <a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getID()) . '&amp;sid=' . intval($strPrevious) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('up_.gif')) . ' ';
					continue;
				}

				$return .= ((is_numeric($strNext) && (!in_array($objModelRow->getID(), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? ' <a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getID()) . '&amp;sid=' . intval($strNext) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('down_.gif')) . ' ';
			}
		}

		// Add paste into/after icons
		if ($this->getDC()->isClipboard())
		{
			$arrClipboard = $this->getDC()->getClipboard();

			// Check if the id is in the ignore list
			if ($arrClipboard['mode'] == 'cut' && in_array($objModelRow->getID(), $arrClipboard['ignoredIDs']))
			{
				switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
				{
					default:
					case 4:
						$return .= ' ';
						$return .= $imagePasteAfter = $this->generateImage('pasteafter_.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"');
						break;

					case 5:
						$return .= ' ';
						$return .= $imagePasteAfter = $this->generateImage('pasteafter_.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"');
						$return .= ' ';
						$return .= $imagePasteInto = $this->generateImage('pasteinto_.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0], 'class="blink"');
						break;
				}
			}
			else
			{

//                $strAdd2Url = "";
//                $strAdd2Url .= 'act=' . $arrClipboard['mode'];
//                $strAdd2Url .= 'act=' . $arrClipboard['mode'];
//
				// Switch mode
				// Add ext. information
				$strAdd2UrlAfter = 'act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $arrClipboard['id'] . '&amp;after=' . $objModelRow->getID() . '&amp;source=' . $arrClipboard['source'] . '&amp;childs=' . $arrClipboard['childs'];
				$strAdd2UrlInto = 'act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $arrClipboard['id'] . '&amp;after=' . $objModelRow->getID() . '&amp;source=' . $arrClipboard['source'] . '&amp;childs=' . $arrClipboard['childs'];

				if ($arrClipboard['pdp'] != '')
				{
					$strAdd2UrlAfter .= '&amp;pdp=' . $arrClipboard['pdp'];
					$strAdd2UrlInto .= '&amp;pdp=' . $arrClipboard['pdp'];
				}

				if ($arrClipboard['cdp'] != '')
				{
					$strAdd2UrlAfter .= '&amp;cdp=' . $arrClipboard['cdp'];
					$strAdd2UrlInto .= '&amp;cdp=' . $arrClipboard['cdp'];
				}

				switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
				{
					default:
					case 4:
						$imagePasteAfter = $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"');
						$return .= ' <a href="'
								. $this->addToUrl($strAdd2UrlAfter)
								. '" title="' . specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0]) . '" onclick="Backend.getScrollOffset()">'
								. $imagePasteAfter
								. '</a> ';
						break;

					case 5:
						$imagePasteAfter = $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"');
						$return .= ' <a href="'
								. $this->addToUrl($strAdd2UrlAfter)
								. '" title="' . specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0]) . '" onclick="Backend.getScrollOffset()">'
								. $imagePasteAfter
								. '</a> ';

						$imagePasteInto = $this->generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0], 'class="blink"');
						$return .= ' <a href="'
								. $this->addToUrl($strAdd2UrlInto)
								. '" title="' . specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0]) . '" onclick="Backend.getScrollOffset()">'
								. $imagePasteInto
								. '</a> ';
						break;
				}
			}
		}
		return trim($return);
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Panel
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	protected function panel()
	{
		if (is_array($this->getDC()->getPanelView()) && count($this->getDC()->getPanelView()) > 0)
		{
			$objTemplate = new BackendTemplate('dcbe_general_panel');
			$objTemplate->action = ampersand($this->Environment->request, true);
			$objTemplate->theme = $this->getTheme();
			$objTemplate->panel = $this->getDC()->getPanelView();

			return $objTemplate->parse();
		}

		return '';
	}

}

?>