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

namespace DcGeneral\View;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Exception\DcGeneralException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\Panel\LimitElementInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\SubmitElementInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\View\ViewInterface;

// TODO: this is not as elegant as it could be.
use DcGeneral\Contao\BackendBindings;

use DcGeneral\DataContainerInterface;

class BackendView implements ViewInterface
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
	protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.</div>";

	// Objects --------------------------------------

	/**
	 * Driver class
	 * @var DataContainerInterface
	 */
	protected $objDC = null;

	/**
	 * A list with all supported languages
	 * @var CollectionInterface
	 */
	protected $objLanguagesSupported = null;

	/**
	 * Used by palette rendering.
	 *
	 * @var array
	 */
	protected $arrStack = array();

	/**
	 * Initialize the object
	 */
	public function __construct()
	{

	}

	/**
	 * @return DataContainerInterface
	 */
	public function getDC()
	{
		return $this->objDC;
	}

	protected $objViewHandler;

	/**
	 * @param DataContainerInterface $objDC
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;

		// Setup our fallback handling.
		switch ($this->getEnvironment()->getDataDefinition()->getSortingMode())
		{
			case 0: // Records are not sorted
			case 1: // Records are sorted by a fixed field
			case 2: // Records are sorted by a switchable field
			case 3: // Records are sorted by the parent table
				$this->objViewHandler = new BackendView\ListView();
				break;
			case 4: // Displays the child records of a parent record (see style sheets module)
				$this->objViewHandler = new BackendView\ParentView();
				break;
			case 5: // Records are displayed as tree (see site structure)
			case 6: // Displays the child records within a tree structure (see articles module)
				$this->objViewHandler = new BackendView\TreeView();
				break;
			default:
				throw new DcGeneralRuntimeException('Unknown sorting mode passed in data definition: ' . $this->getEnvironment()->getDataDefinition()->getSortingMode());
		}
		$this->objViewHandler->setDC($objDC);
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkClipboard()
	{
		return $this->objViewHandler->checkClipboard();
	}

	/**
	 *
	 * @return \DcGeneral\EnvironmentInterface
	 */
	protected function getEnvironment()
	{
		return $this->getDC()->getEnvironment();
	}

	/**
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	protected function getDataDefinition()
	{
		return $this->getEnvironment()->getDataDefinition();
	}

	/**
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	protected function getCurrentCollection()
	{
		return $this->getEnvironment()->getCurrentCollection();
	}

	/**
	 * @return ModelInterface
	 */
	protected function getCurrentModel()
	{
		return $this->getEnvironment()->getCurrentModel();
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
		if ($objDataProvider instanceof MultiLanguageDriverInterface)
		{
			/** @var MultiLanguageDriverInterface $objDataProvider */
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

	protected function getTemplate($strTemplate)
	{
		return new ContaoBackendViewTemplate($strTemplate);
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
		return $this->objViewHandler->copy();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function copyAll()
	{
		return $this->objViewHandler->copyAll();
	}

	/**
	 * @see edit()
	 * @return stirng
	 */
	public function create()
	{
		return $this->objViewHandler->create();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function cut()
	{
		return $this->objViewHandler->cut();
	}

	public function paste()
	{
		return $this->objViewHandler->paste();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function cutAll()
	{
		return $this->objViewHandler->cutAll();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function delete()
	{
		return $this->objViewHandler->delete();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function move()
	{
		return $this->objViewHandler->move();
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function undo()
	{
		return $this->objViewHandler->undo();
	}

	/**
	 * Generate the view for edit
	 *
	 * @return string
	 */
	public function edit()
	{
		return $this->objViewHandler->edit();
	}

	/**
	 * Show Informations about a data set
	 *
	 * @return String
	 */
	public function show()
	{
		return $this->objViewHandler->show();
	}

	/**
	 * Show all entries from one table
	 *
	 * @return string HTML
	 */
	public function showAll()
	{
		return $this->objViewHandler->showAll();
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * AJAX Calls
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	public function ajaxTreeView($intID, $intLevel)
	{
		return $this->objViewHandler->ajaxTreeView($intID, $intLevel);
	}

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
		BackendBindings::loadLanguageFile($this->getDC()->getParentTable());
		BackendBindings::loadDataContainer($this->getDC()->getParentTable());

		// Get parent DC Driver
		// TODO: who ever did this, you can't be serious - REFACTOR!
		$objParentDC = new \DC_General($this->getDC()->getParentTable());
		$this->parentDca = $objParentDC->getDCA();

		// Add template
		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_parentView');

		$objTemplate->tableName = strlen($this->objDC->getTable())? $this->objDC->getTable() : 'none';
		$objTemplate->collection = $this->getCurrentCollection();
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
			'content' => BackendBindings::generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['editheader'][0]),
			'href' => preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->getDC()->getParentTable()) ? '&amp;table=' . $this->getDC()->getParentTable() : ''), BackendBindings::addToUrl('act=edit')),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['editheader'][1])
		);

		$objTemplate->pasteNew = array(
			'content' => BackendBindings::generateImage('new.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0]),
			'href' => BackendBindings::addToUrl('act=create&amp;mode=2&amp;pid=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID() . '&amp;id=' . $this->intId),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pastenew'][0])
		);

		$objTemplate->pasteAfter = array(
			'content' => BackendBindings::generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0], 'class="blink"'),
			'href' => BackendBindings::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID() . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')),
			'title' => specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][0])
		);

		$objTemplate->notDeletable = $this->getDC()->arrDCA['config']['notDeletable'];
		$objTemplate->notEditable = $this->getDC()->arrDCA['config']['notEditable'];
		$objTemplate->notEditableParent = $this->parentDca['config']['notEditable'];

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
		}

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
		for ($i = 0; $i < $this->getCurrentCollection()->length(); $i++)
		{
			// Get model
			$objModel = $this->getCurrentCollection()->get($i);

			// Set in DC as current for callback and co.
			$this->getEnvironment()->setCurrentModel($objModel);

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
				$strPrevious = ((!is_null($this->getCurrentCollection()->get($i - 1))) ? $this->getCurrentCollection()->get($i - 1)->getID() : null);
				$strNext = ((!is_null($this->getCurrentCollection()->get($i + 1))) ? $this->getCurrentCollection()->get($i + 1)->getID() : null);

				$buttons = $this->generateButtons($objModel, $this->getDC()->getTable(), $this->getDC()->getEnvironment()->getRootIds(), false, null, $strPrevious, $strNext);

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
	 * @param ModelInterface $objModel
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
			$arrReturn[] = ' <a href="' . BackendBindings::addToUrl('act=create&amp;mode=1&amp;pid=' . $objModel->getID() . '&amp;id=' . $this->getDC()->getCurrentParentCollection()->get(0)->getID()) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pastenew'][1], $row[$i]['id'])) . '">' . $imagePasteNew . '</a>';
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
			$arrReturn[] = ' <a href="' . BackendBindings::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
		}

		// TODO clipboard
		// Paste buttons
		elseif ($blnClipboard)
		{
			$arrReturn[] = ' <a href="' . BackendBindings::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id'] . '&amp;id=' . $arrClipboard['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
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
		throw new new DcGeneralException('Do not use the default view anymore.');

		// Set label
		$this->setListViewLabel();

		// Generate buttons
		foreach ($this->getCurrentCollection() as $objModelRow)
		{
			$objModelRow->setMeta(DCGE::MODEL_BUTTONS, $this->generateButtons($objModelRow, $this->getDC()->getTable(), $this->getDC()->getEnvironment()->getRootIds()));
		}

		// Add template
		if ($this->getDC()->getFirstSorting() == 'sorting')
		{
			// FIXME: dependency injection or rather template factory?
			$objTemplate = new \BackendTemplate('dcbe_general_listView_sorting');
		}
		else
		{
			// FIXME: dependency injection or rather template factory?
			$objTemplate = new \BackendTemplate('dcbe_general_listView');
		}

		$objTemplate->tableName = strlen($this->objDC->getTable())? $this->objDC->getTable() : 'none';
		$objTemplate->collection = $this->getCurrentCollection();
		$objTemplate->select = $this->getDC()->isSelectSubmit();
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->mode = $this->getDC()->arrDCA['list']['sorting']['mode'];
		$objTemplate->tableHead = $this->getTableHead();
		$objTemplate->notDeletable = $this->getDC()->arrDCA['config']['notDeletable'];
		$objTemplate->notEditable = $this->getDC()->arrDCA['config']['notEditable'];

		// Set dataprovider from current and parent
		$objTemplate->pdp = '';
		$objTemplate->cdp = $this->getDC()->getDataProvider('self')->getEmptyModel()->getProviderName();

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
		}

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
		$strLabelText = (strlen($this->getDC()->arrDCA['config']['label']) == 0 ) ? 'DC General Tree BackendView Ultimate' : $this->getDC()->arrDCA['config']['label'];
		$strLabelIcon = strlen($this->getDC()->arrDCA['list']['sorting']['icon']) ? $this->getDC()->arrDCA['list']['sorting']['icon'] : 'pagemounts.gif';

		// Rootpage pasteinto
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$objClipboard = $this->getEnvironment()->getClipboard();
			// TODO: @CS we definately need into and after handling here instead of different modes.
			$imagePasteInto = BackendBindings::generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0], 'class="blink"');
			$strRootPasteinto = '<a href="' . BackendBindings::addToUrl('act=' . $objClipboard->getMode() . '&amp;mode=2&amp;after=0&amp;pid=0&amp;id=' . $arrClipboard['id'] . '&amp;childs=' . $arrClipboard['childs']) . '" title="' . specialchars($GLOBALS['TL_LANG'][$this->getDC()->getTable()]['pasteinto'][0]) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto . '</a> ';

			// Callback for paste btn.
			$strButtonCallback = $this->getEnvironment()->getCallbackHandler()->pasteButtonCallback(
				$this->getDC()->getDataProvider($this->getDC()->getTable())->getEmptyModel()->getPropertiesAsArray(),
				$this->getDC()->getTable(),
				false,
				$arrClipboard,
				null,
				null
			);

			if ($strButtonCallback !== false)
			{
				$strRootPasteinto = $strButtonCallback;
			}
		}

		// Create treeview
		$strHTML = $this->generateTreeView($this->getCurrentCollection(), $intMode, $treeClass);

		// Build template
		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_treeview');
		$objTemplate->treeClass = 'tl_' . $treeClass;
		$objTemplate->tableName = strlen($this->objDC->getTable())? $this->objDC->getTable() : 'none';
		$objTemplate->strLabelIcon = BackendBindings::generateImage($strLabelIcon);
		$objTemplate->strLabelText = $strLabelText;
		$objTemplate->strHTML = $strHTML;
		$objTemplate->intMode = $intMode;
		$objTemplate->strRootPasteinto = $strRootPasteinto;

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
		}

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

			// FIXME: dependency injection or rather template factory?
			$objEntryTemplate = new \BackendTemplate('dcbe_general_treeview_entry');
			$objEntryTemplate->objModel = $objModel;
			$objEntryTemplate->intMode = $intMode;
			$objEntryTemplate->strToggleID = $strToggleID;

			$strHTML .= $objEntryTemplate->parse();
			$strHTML .= "\n";

			if ($objModel->getMeta(DCGE::TREE_VIEW_HAS_CHILDS) == true && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) == true)
			{
				// FIXME: dependency injection or rather template factory?
				$objChildTemplate = new \BackendTemplate('dcbe_general_treeview_child');
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
		$this->arrRootPalette = $this->getDC()->getEnvironment()->getCallbackHandler()->parseRootPaletteCallback($this->arrRootPalette);
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
			$varValue = $this->getCurrentModel()->getProperty($strSelector);
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

			$varValue = $this->getCurrentModel()->getProperty($strField);
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
			throw new DcGeneralRuntimeException("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
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
		$arrFieldsetStates = $this->getDC()->getEnvironment()->getInputProvider()->getPersistentValue('fieldset_states');
		$arrFieldsetStates = $arrFieldsetStates[$this->getDC()->getEnvironment()->getDataDefinition()->getName()];

		if (!is_array($arrFieldsetStates))
		{
			$arrFieldsetStates = array();
		}

		$arrRootPalette = $this->arrRootPalette;

		$blnFirst = true;

		foreach ($arrRootPalette as &$arrFieldset)
		{
			if($blnFirst)
			{
				$strClass = 'tl_tbox';
				$blnFirst = false;
			}
			else
			{
				$strClass = 'tl_box';
			}

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

				// TODO: is this check needed? What widget might be of "non-widget" type?
				if (!$objWidget instanceof \Widget)
				{
					/* TODO wtf is this shit? A widget **cannot** be converted to a string!
					echo $objWidget;
					continue;
					*/
				}

				$arrConfig = $this->getDC()->getFieldDefinition($varField);

				$strClass = $arrConfig['eval']['tl_class'];

				// TODO: this should be correctly specified in DCAs, notyetbepatient
				if($arrConfig['inputType'] == 'checkbox' && !$arrConfig['eval']['multiple'] && strpos($strClass, 'w50') !== false && strpos($strClass, 'cbx') === false)
				{
					$strClass .= ' cbx';
				}

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
				// FIXME: dependency injection or rather template factory?
				$objTemplateFoo = new \BackendTemplate($strFieldTemplate);
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

		foreach ($this->getCurrentCollection() as $objModelRow)
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
				// Use the fild flag, if given
				else if ($this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'] != '')
				{
					$sortingMode = $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'];
				}
				// ToDo: Should we remove this, because we allready have the fallback ?
				// If the current First sorting is the default one use the global flag
				else if ($this->getDC()->getFirstSorting() == $orderBy[0])
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
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
	 * @return string
	 */
	protected function generateHeaderButtons($strButtonId)
	{
		$globalOperations = $this->getDC()->arrDCA['list']['global_operations'];
		$arrReturn        = array();

		if (!is_array($globalOperations))
		{
			$globalOperations = array();
		}

		// Make Urls absolute.
		foreach ($globalOperations as $k => $v)
		{
			$globalOperations[$k]['href'] = BackendBindings::addToUrl($v['href']);
		}

		// Check if we have the select mode
		if (!$this->getDC()->isSelectSubmit())
		{
			$addButton = false;
			$strHref   = '';

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
						$strHref = BackendBindings::addToUrl($strHref . '&amp;id=&amp;act=create&amp;pid=' . $this->getDC()->getId());
					}
					else
					{
						$strHref = BackendBindings::addToUrl('act=create');
					}

					$addButton = !$this->getDataDefinition()->isClosed();
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

					$strHref   = BackendBindings::addToUrl(sprintf('act=paste&amp;mode=create&amp;cdp=%s&amp;pdp=%s', $strCDP, $strPDP));
					$addButton = !($this->getDataDefinition()->isClosed() || $this->getEnvironment()->getClipboard()->isNotEmpty());

					break;
			}

			if ($addButton)
			{
				$globalOperations = array_merge(
					array(
						'button_new'     => array
						(
							'class'      => 'header_new',
							'accesskey'  => 'n',
							'href'       => $strHref,
							'attributes' => 'onclick="Backend.getScrollOffset();"',
							'title'      => $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][1],
							'label'      => $GLOBALS['TL_LANG'][$this->getDC()->getTable()]['new'][0]
						)
					),
					$globalOperations
				);
			}

		}

		// add clear clipboard button if needed.
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$globalOperations = array_merge(
				array(
					'button_clipboard'     => array
					(
						'class'      => 'header_clipboard',
						'accesskey'  => 'x',
						'href'       => BackendBindings::addToUrl('clipboard=1'),
						'title'      => $GLOBALS['TL_LANG']['MSC']['clearClipboard'],
						'label'      => $GLOBALS['TL_LANG']['MSC']['clearClipboard']
					)
				)
				, $globalOperations
			);
		}

		// Add back button
		if ($this->getDC()->isSelectSubmit() || $this->getDC()->getParentTable())
		{
			$globalOperations = array_merge(
				array(
					'back_button'    => array
					(
						'class'      => 'header_back',
						'accesskey'  => 'b',
						'href'       => BackendBindings::getReferer(true, $this->getDC()->getParentTable()),
						'attributes' => 'onclick="Backend.getScrollOffset();"',
						'title'      => $GLOBALS['TL_LANG']['MSC']['backBT'],
						'label'      => $GLOBALS['TL_LANG']['MSC']['backBT']
					)
				),
				$globalOperations
			);
		}

		// Add global buttons
		foreach ($globalOperations as $k => $v)
		{
			$v          = is_array($v) ? $v : array($v);
			$label      = is_array($v['label']) ? $v['label'][0] : $v['label'];
			$title      = is_array($v['label']) ? $v['label'][1] : $v['label'];
			$attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';
			$accessKey  = strlen($v['accesskey']) ? trim($v['accesskey']) : '';
			$href       = $v['href'];

			if (!strlen($label))
			{
				$label = $k;
			}

			$buttonEvent = new GetGlobalButtonEvent();
			$buttonEvent
				->setAccessKey($accessKey)
				->setAttributes($attributes)
				->setClass($v['class'])
				->setEnvironment($this->getEnvironment())
				->setKey($k)
				->setHref($href)
				->setLabel($label)
				->setTitle($title);

			$this->dispatchEvent(GetGlobalButtonEvent::NAME, $buttonEvent);

			// Allow to override the button entirely.
			$html =$buttonEvent->getHtml();
			if (!is_null($html))
			{
				if (!empty($html))
				{
					$arrReturn[$buttonEvent->getKey()] = $html;
				}
				continue;
			}

			// Use the view native button building.
			$arrReturn[$k] = sprintf(
				'<a href="%s" class="%s" title="%s"%s>%s</a> ',
				$buttonEvent->getHref(),
				$buttonEvent->getClass(),
				specialchars($buttonEvent->getTitle()),
				$buttonEvent->getAttributes(),
				$buttonEvent->getLabel()
			);
		}

		$buttonsEvent = new GetGlobalButtonsEvent();
		$buttonsEvent
			->setEnvironment($this->getEnvironment())
			->setButtons($arrReturn);
		$this->dispatchEvent(GetGlobalButtonsEvent::NAME, $buttonsEvent);

		return '<div id="' . $strButtonId . '">' . implode(' &nbsp; :: &nbsp; ', $buttonsEvent->getButtons()) . '</div>';
	}

	/**
	 * Compile buttons from the table configuration array and return them as HTML
	 *
	 * @param ModelInterface $objModelRow
	 * @param string $strTable
	 * @param array $arrRootIds
	 * @param boolean $blnCircularReference
	 * @param array $arrChildRecordIds
	 * @param int $strPrevious
	 * @param int $strNext
	 * @return string
	 */
	protected function generateButtons(ModelInterface $objModelRow, $strTable, $arrRootIds = array(), $blnCircularReference = false, $arrChildRecordIds = null, $strPrevious = null, $strNext = null)
	{
		$arrOperations = $this->getDataDefinition()->getOperationNames();
		if (!$arrOperations)
		{
			return '';
		}

		$arrButtons = array();
		foreach ($arrOperations as $operation)
		{
			$objOperation = $this->getDataDefinition()->getOperation($operation);

			// Set basic information.
			$opLabel = $objOperation->getLabel();
			if (strlen($opLabel[0]) )
			{
				$label = $opLabel[0];
				$title = sprintf($opLabel[1], $objModelRow->getID());
			}
			else
			{
				$label = $operation;
				$title = sprintf('%s id %s', $operation, $objModelRow->getID());
			}

			$strAttributes = $objOperation->getAttributes();
			$attributes    = '';
			if (strlen($strAttributes))
			{
				$attributes = ltrim(sprintf($strAttributes, $objModelRow->getID()));
			}

			// Cut needs some special information.
			if ($operation == 'cut')
			{
				// Generate all buttons except "move up" and "move down" buttons
				if ($operation == 'move')
				{
					continue;
				}

				// Get data provider from current and parent
				$strCDP = $objModelRow->getProviderName();
				$strPDP = $objModelRow->getMeta(DCGE::MODEL_PTABLE);

				$strAdd2Url = "";

				// Add url + id + currentDataProvider
				$strAdd2Url .= $objOperation->getHref() . '&amp;cdp=' . $strCDP;

				// Add parent provider if exists.
				if ($strPDP != null)
				{
					$strAdd2Url .= '&amp;pdp=' . $strPDP;
				}

				// If we have a id add it, used for mode 4 and all parent -> current views
				if ($this->getEnvironment()->getInputProvider()->hasParameter('id'))
				{
					$strAdd2Url .= '&amp;id=' . $this->getEnvironment()->getInputProvider()->getParameter('id');
				}

				// Source is the id of the element which should move
				$strAdd2Url .= '&amp;source=' . $objModelRow->getID();

				$strHref = BackendBindings::addToUrl($strAdd2Url);
			}
			else
			{
				// TODO: Shall we interface this option?
				$idParam = $objOperation->get('idparam');
				if ($idParam)
				{
					$idParam = sprintf('id=&amp;%s=%s', $idParam, $objModelRow->getID());
				}
				else
				{
					$idParam = sprintf('id=%s', $objModelRow->getID());
				}

				$strHref = BackendBindings::addToUrl($objOperation->getHref() . '&amp;' . $idParam);
			}

			$buttonEvent = new GetOperationButtonEvent();
			$buttonEvent
				->setObjOperation($objOperation)
				->setObjModel($objModelRow)
				->setAttributes($attributes)
				->setEnvironment($this->getEnvironment())
				->setLabel($label)
				->setTitle($title)
				->setHref($strHref)
				->setChildRecordIds($arrChildRecordIds)
				->setCircularReference($blnCircularReference)
				->setPrevious($strPrevious)
				->setNext($strNext);

			$this->dispatchEvent(GetOperationButtonEvent::NAME, $buttonEvent);

			// If the event created a button, use it.
			if (!is_null($buttonEvent->getHtml()))
			{
				$arrButtons[] = trim($buttonEvent->getHtml());
				continue;
			}

			$arrButtons[] = sprintf(' <a href="%s" title="%s" %s>%s</a>',
				$buttonEvent->getHref(),
				specialchars($buttonEvent->getTitle()),
				$buttonEvent->getAttributes(),
				BackendBindings::generateImage($objOperation->getIcon(), $buttonEvent->getLabel())
			);
		}

		// Add paste into/after icons
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$objClipboard = $this->getEnvironment()->getClipboard();

			// Check if the id is in the ignore list
			if ($objClipboard->isCut() && in_array($objModelRow->getID(), $objClipboard->getCircularIds()))
			{
				switch ($this->getDataDefinition()->getSortingMode())
				{
					case 4:
						$arrButtons[] = BackendBindings::generateImage('pasteafter_.gif', $GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteafter'][0], 'class="blink"');
						break;

					case 5:
						$arrButtons[] = BackendBindings::generateImage('pasteafter_.gif', $GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteafter'][0], 'class="blink"');
						$arrButtons[] = BackendBindings::generateImage('pasteinto_.gif', $GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteinto'][0], 'class="blink"');
						break;
					default:
				}
			}
			else
			{
				$strMode = $objClipboard->getMode();

				// Switch mode
				// Add ext. information
				$strAdd2UrlAfter = sprintf('act=%s&amp;mode=1&amp;after=%s&amp;',
					$strMode,
					$objModelRow->getID()
				);

				$strAdd2UrlInto = sprintf('act=%s&amp;mode=2&amp;into=%s&amp;',
					$strMode,
					$objModelRow->getID()
				);

				$buttonEvent = new GetPasteButtonCallbackEvent();
				$buttonEvent
					->setEnvironment($this->getEnvironment())
					->setCircularReference(false)
					->setPrevious(null)
					->setNext(null);

				$this->dispatchEvent(GetPasteButtonCallbackEvent::NAME, $buttonEvent);

				if (!is_null($buttonEvent->getHtmlPasteInto()))
				{
					$arrButtons[] = $buttonEvent->getHtmlPasteAfter();
					$arrButtons[] = $buttonEvent->getHtmlPasteInto();
				}
				else
				{
					$arrButtons[] = sprintf(' <a href="%s" title="%s" %s>%s</a>',
						BackendBindings::addToUrl($strAdd2UrlAfter),
						specialchars($GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteafter'][0]),
						'onclick="Backend.getScrollOffset()"',
						BackendBindings::generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteafter'][0], 'class="blink"')
					);

					if ($this->getDataDefinition()->getSortingMode() == 5)
					{
						$arrButtons[] = sprintf(' <a href="%s" title="%s" %s>%s</a>',
							BackendBindings::addToUrl($strAdd2UrlInto),
							specialchars($GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteinto'][0]),
							'onclick="Backend.getScrollOffset()"',
							BackendBindings::generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$objModelRow->getProviderName()]['pasteinto'][0], 'class="blink"')
						);
					}
				}
			}
		}

		return implode(' ', $arrButtons);
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Panel
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	protected function panel()
	{
		if ($this->getDC()->getPanelInformation() === null)
		{
			throw new DcGeneralRuntimeException('No panel information stored in data container.');
		}

		$arrPanels = array();
		foreach ($this->getDC()->getPanelInformation() as $objPanel)
		{
			$arrPanel = array();
			foreach ($objPanel as $objElement)
			{
				$objElementTemplate = null;
				if ($objElement instanceof FilterElementInterface)
				{
					$objElementTemplate = $this->getTemplate('dcbe_general_panel_filter');
				}
				elseif ($objElement instanceof LimitElementInterface)
				{
					$objElementTemplate = $this->getTemplate('dcbe_general_panel_limit');
				}
				elseif ($objElement instanceof SearchElementInterface)
				{
					$objElementTemplate = $this->getTemplate('dcbe_general_panel_search');
				}
				elseif ($objElement instanceof SortElementInterface)
				{
					$objElementTemplate = $this->getTemplate('dcbe_general_panel_sort');
				}
				elseif ($objElement instanceof SubmitElementInterface)
				{
					$objElementTemplate = $this->getTemplate('dcbe_general_panel_submit');
				}
				$objElement->render($objElementTemplate);

				$arrPanel[] = $objElementTemplate->parse();
			}
			$arrPanels[] = $arrPanel;
		}

		if (count($arrPanels))
		{
			$objTemplate = $this->getTemplate('dcbe_general_panel');
			$objTemplate->action = ampersand($this->getDC()->getInputProvider()->getRequestUrl(), true);
			// FIXME: dependency injection
//			$objTemplate->theme = $this->getTheme();
			$objTemplate->panel = $arrPanels;

			return $objTemplate->parse();
		}

		return '';
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Breadcrumb
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Get the breadcrumb navigation by callback
	 *
	 * @return string
	 */
	protected function breadcrumb()
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

		// Include the breadcrumb css
		$GLOBALS['TL_CSS'][] = 'system/modules/generalDriver/html/css/generalBreadcrumb.css';

		// Build template
		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_breadcrumb');
		$objTemplate->elements = $arrReturn;

		return $objTemplate->parse();
	}

}
