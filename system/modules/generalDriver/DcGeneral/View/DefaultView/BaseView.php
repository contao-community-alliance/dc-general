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

namespace DcGeneral\View\DefaultView;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\DC_General;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\Panel\LimitElementInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\SubmitElementInterface;
use DcGeneral\View\ContaoBackendViewTemplate;
use DcGeneral\View\DefaultView\Events\GetGlobalButtonEvent;
use DcGeneral\View\DefaultView\Events\GetGlobalButtonsEvent;
use DcGeneral\View\DefaultView\Events\GetGroupHeaderEvent;
use DcGeneral\View\DefaultView\Events\GetOperationButtonEvent;
use DcGeneral\View\DefaultView\Events\GetPasteButtonEvent;
use DcGeneral\View\ViewInterface;

// TODO: this is not as elegant as it could be.
use DcGeneral\Contao\BackendBindings;

use DcGeneral\DataContainerInterface;

class BaseView implements ViewInterface
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

	/**
	 * @param string                                   $eventName
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 */
	protected function dispatchEvent($eventName, $event)
	{
		global $container;
		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher       = $container['event-dispatcher'];

		// First, try to dispatch to all DCA registered subscribers.
		$dispatcher->dispatch(
			sprintf(
				'%s[%s]',
				$eventName,
				$this->getEnvironment()->getDataDefinition()->getName()
			),
			$event
		);

		var_dump(sprintf(
			'%s[%s]',
			$eventName,
			$this->getEnvironment()->getDataDefinition()->getName()
		));

		// Second, try to dispatch to all globally registered subscribers.
		if (!$event->isPropagationStopped())
		{
			$dispatcher->dispatch($eventName, $event);
			var_dump($eventName);
		}
	}

	/**
	 * @param DataContainerInterface $objDC
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
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


	/**
	 * Return the formatted value for use in group headers as string
	 *
	 * @param string  $field
	 *
	 * @param mixed   $value
	 *
	 * @param integer $mode
	 *
	 * @return string
	 */
	public function formatCurrentValue($field, $value, $mode)
	{
		$property   = $this->getDataDefinition()->getProperty($field);

		// No property? Get out!
		if (!$property)
		{
			return '-';
		}

		$evaluation = $property->getEvaluation();
		$remoteNew  = '';

		if ($property->get('inputType') == 'checkbox' && !$evaluation['multiple'])
		{
			$remoteNew = ($value != '') ? ucfirst($GLOBALS['TL_LANG']['MSC']['yes']) : ucfirst($GLOBALS['TL_LANG']['MSC']['no']);
		}
		elseif ($property->get('foreignKey'))
		{
			// TODO: case handling

			if($objParentModel->hasProperties())
			{
				$remoteNew = $objParentModel->getProperty('value');
			}

		}
		elseif (in_array($mode, array(1, 2)))
		{
			$remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, 1)) : '-';
		}
		elseif (in_array($mode, array(3, 4)))
		{
			if ($property->get('length'))
			{
				$length = $property->get('length');
			}
			else
			{
				$length = 2;
			}

			$remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, $length)) : '-';
		}
		elseif (in_array($mode, array(5, 6)))
		{
			$remoteNew = ($value != '') ? BackendBindings::parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '-';
		}
		elseif (in_array($mode, array(7, 8)))
		{
			$remoteNew = ($value != '') ? date('Y-m', $value) : '-';
			$intMonth = ($value != '') ? (date('m', $value) - 1) : '-';

			if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
			{
				$remoteNew = ($value != '') ? $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $value) : '-';
			}
		}
		elseif (in_array($mode, array(9, 10)))
		{
			$remoteNew = ($value != '') ? date('Y', $value) : '-';
		}
		else
		{
			if ($property->get('inputType') == 'checkbox' && !$evaluation['multiple'])
			{
				$remoteNew = ($value != '') ? $field : '';
			}
			elseif (is_array($property->get('reference')))
			{
				$reference = $property->get('reference');
				$remoteNew = $reference[$value];
			}
			elseif (array_is_assoc($property->get('options')))
			{
				$options   = $property->get('options');
				$remoteNew = $options[$value];
			}
			else
			{
				$remoteNew = $value;
			}

			if (is_array($remoteNew))
			{
				$remoteNew = $remoteNew[0];
			}

			if (empty($remoteNew))
			{
				$remoteNew = '-';
			}
		}

		return $remoteNew;
	}

	/**
	 * Return the formatted group header as string.
	 *
	 * @param                                $field
	 *
	 * @param                                $value
	 *
	 * @param                                $mode
	 *
	 * @param \DcGeneral\Data\ModelInterface $objModelRow
	 *
	 * @return string
	 */
	public function formatGroupHeader($field, $value, $mode, ModelInterface $objModelRow)
	{
		$group = '';
		static $lookup = array();

		if (array_is_assoc($this->arrDCA['fields'][$field]['options']))
		{
			$group = $this->arrDCA['fields'][$field]['options'][$value];
		}
		else if (is_array($this->arrDCA['fields'][$field]['options_callback']))
		{
			if (!isset($lookup[$field]))
			{
				$lookup[$field] = $this->getEnvironment()->getCallbackHandler()->optionsCallback($field);
			}

			$group = $lookup[$field][$value];
		}
		else
		{
			$group = is_array($this->arrDCA['fields'][$field]['reference'][$value]) ? $this->arrDCA['fields'][$field]['reference'][$value][0] : $this->arrDCA['fields'][$field]['reference'][$value];
		}

		if (empty($group))
		{
			$group = is_array($this->arrDCA[$value]) ? $this->arrDCA[$value][0] : $this->arrDCA[$value];
		}

		if (empty($group))
		{
			$group = $value;

			if ($this->arrDCA['fields'][$field]['eval']['isBoolean'] && $value != '-')
			{
				$group = is_array($this->arrDCA['fields'][$field]['label']) ? $this->arrDCA['fields'][$field]['label'][0] : $this->arrDCA['fields'][$field]['label'];
			}
		}

		$event = new GetGroupHeaderEvent();

		$event
			->setEnvironment($this->getEnvironment())
			->setModel($objModelRow)
			->setGroupField($group)
			->setSortingMode($mode)
			->setValue($field);

		$this->dispatchEvent(GetGroupHeaderEvent::NAME, $event);

		$group = $event->getGroupField();

		return $group;
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
		$objDataProvider = $this->getEnvironment()->getDataDriver();

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
		return vsprintf($this->notImplMsg, 'copy - Mode');
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function copyAll()
	{
		return vsprintf($this->notImplMsg, 'copyAll - Mode');
	}

	/**
	 * @see edit()
	 * @return string
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
		return vsprintf($this->notImplMsg, 'cut - Mode');
	}

	public function paste()
	{

	}

	/**
	 * @todo All
	 * @return type
	 */
	public function cutAll()
	{
		return vsprintf($this->notImplMsg, 'cutAll - Mode');
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function delete()
	{
		return vsprintf($this->notImplMsg, 'delete - Mode');
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function move()
	{
		return vsprintf($this->notImplMsg, 'move - Mode');
	}

	/**
	 * @todo All
	 * @return type
	 */
	public function undo()
	{
		return vsprintf($this->notImplMsg, 'undo - Mode');
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

		$model = $this->getEnvironment()->getCurrentModel();

		// Get all selectors
		$this->arrStack[] = $this->getEnvironment()->getDataDefinition()->getSubPalettes();
		$this->calculateSelectors($this->arrStack[0]);
		$this->parseRootPalette();

		include(TL_ROOT . '/system/config/languages.php');

		// ToDo: What is this $languages[$this->strCurrentLanguage];

		if ($model->getId())
		{
			$strHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], 'ID ' . $model->getId());
		}
		else
		{
			// TODO: new language string for "new" model?
			$strHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], '');
		}

		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_edit');
		$objTemplate->setData(array(
			'fieldsets' => $this->generateFieldsets('dcbe_general_field', array()),
			'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'versions' => $this->getEnvironment()->getDataDriver()->getVersions($model->getId()),
			'language' => $this->objLanguagesSupported,
			'subHeadline' => $strHeadline,
			'languageHeadline' => strlen($this->strCurrentLanguage) != 0 ? $langsNative[$this->strCurrentLanguage] : '',
			'table' => $this->getDataDefinition()->getName(),
			'enctype' => $this->getDC()->isUploadable() ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
			//'onsubmit' => implode(' ', $this->onsubmit),
			'error' => $this->noReload,
			'buttons' => $this->getDC()->getButtonsDefinition(),
			'buttonLables' => $this->getDC()->getButtonLabels(),
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

		// Init
		$fields = array();
		$arrFieldValues = array();
		$arrFieldLabels = array();
		$allowedFields = array('pid', 'sorting', 'tstamp');

		foreach ($this->getCurrentModel() as $key => $value)
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
			// TODO: we should examine the palette here and hide irrelevant fields.

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
			$arrFieldValues[$strFieldName] = $this->getDC()->getReadableFieldValue($strFieldName, deserialize($this->getCurrentModel()->getProperty($strFieldName)));

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
		// FIXME: dependency injection or rather template factory?
		$objTemplate            = new \BackendTemplate("dcbe_general_show");
		$objTemplate->headline  = sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($this->getDC()->getId() ? 'ID ' . $this->getDC()->getId() : ''));
		$objTemplate->arrFields = $arrFieldValues;
		$objTemplate->arrLabels = $arrFieldLabels;
		$objTemplate->language  = $this->objLanguagesSupported;

		return $objTemplate->parse();
	}

	/**
	 * Show all entries from one table
	 *
	 * @return string HTML
	 */
	public function showAll()
	{
		return vsprintf($this->notImplMsg, 'showAll - Mode ' . $this->getEnvironment()->getDataDefinition()->getSortingMode());
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

		$strHTML = $this->generateTreeView($this->getCurrentCollection(), $this->getDC()->arrDCA['list']['sorting']['mode'], $treeClass);

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
			throw new \RuntimeException("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
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

				// TODO: is this check needed? What widget might be of "non-widget" type?
				if (!$objWidget instanceof \Contao\Widget)
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
					if (strlen($this->getEnvironment()->getDataDefinition()->getParentDriverName()))
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
					$strCDP = $this->getEnvironment()->getDataDriver()->getEmptyModel()->getProviderName();

					if ($this->getEnvironment()->getDataDefinition()->getParentDriverName() != null)
					{
						$strPDP = $this->getEnvironment()->getDataDriver($this->getEnvironment()->getDataDefinition()->getParentDriverName())->getEmptyModel()->getProviderName();
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
		if ($this->getDC()->isSelectSubmit() || $this->getEnvironment()->getDataDefinition()->getParentDriverName())
		{
			$globalOperations = array_merge(
				array(
					'back_button'    => array
					(
						'class'      => 'header_back',
						'accesskey'  => 'b',
						'href'       => BackendBindings::getReferer(true, $this->getEnvironment()->getDataDefinition()->getParentDriverName()),
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
	 * @param \DcGeneral\DataDefinition\OperationInterface $objOperation
	 *
	 * @param \DcGeneral\Data\ModelInterface               $objModel
	 *
	 * @param bool                                         $blnCircularReference
	 *
	 * @param array                                        $arrChildRecordIds
	 *
	 * @param string                                       $strPrevious
	 *
	 * @param string                                       $strNext
	 *
	 * @return string
	 */
	protected function buildOperation($objOperation, $objModel, $blnCircularReference, $arrChildRecordIds, $strPrevious, $strNext)
	{
		// Set basic information.
		$opLabel = $objOperation->getLabel();
		if (strlen($opLabel[0]) )
		{
			$label = $opLabel[0];
			$title = sprintf($opLabel[1], $objModel->getID());
		}
		else
		{
			$label = $objOperation->getName();
			$title = sprintf('%s id %s', $label, $objModel->getID());
		}

		$strAttributes = $objOperation->getAttributes();
		$attributes    = '';
		if (strlen($strAttributes))
		{
			$attributes = ltrim(sprintf($strAttributes, $objModel->getID()));
		}

		// Cut needs some special information.
		if ($objOperation->getName() == 'cut')
		{
			// Get data provider from current and parent
			$strCDP = $objModel->getProviderName();
			$strPDP = $objModel->getMeta(DCGE::MODEL_PTABLE);

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
			$strAdd2Url .= '&amp;source=' . $objModel->getID();

			$strHref = BackendBindings::addToUrl($strAdd2Url);
		}
		else
		{
			// TODO: Shall we interface this option?
			$idParam = $objOperation->get('idparam');
			if ($idParam)
			{
				$idParam = sprintf('id=&amp;%s=%s', $idParam, $objModel->getID());
			}
			else
			{
				$idParam = sprintf('id=%s', $objModel->getID());
			}

			$strHref = BackendBindings::addToUrl($objOperation->getHref() . '&amp;' . $idParam);
		}

		$buttonEvent = new GetOperationButtonEvent();
		$buttonEvent
			->setObjOperation($objOperation)
			->setObjModel($objModel)
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
			return trim($buttonEvent->getHtml());
		}

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$buttonEvent->getHref(),
			specialchars($buttonEvent->getTitle()),
			$buttonEvent->getAttributes(),
			BackendBindings::generateImage($objOperation->getIcon(), $buttonEvent->getLabel())
		);
	}

	public static function renderPasteIntoButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteInto()))
		{
			return $event->getHtmlPasteInto();
		}

		$strLabel = $GLOBALS['TL_LANG'][$event->getModel()->getProviderName()]['pasteinto'][0];
		if ($event->isPasteIntoDisabled())
		{
			return BackendBindings::generateImage('pasteinto_.gif', $strLabel, 'class="blink"');
		}

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
				$event->getHrefInto(),
				specialchars($strLabel),
				'onclick="Backend.getScrollOffset()"',
				BackendBindings::generateImage('pasteinto.gif', $strLabel, 'class="blink"')
			);
	}

	public static function renderPasteAfterButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteAfter()))
		{
			return $event->getHtmlPasteAfter();
		}

		$strLabel = $GLOBALS['TL_LANG'][$event->getModel()->getProviderName()]['pasteafter'][0];
		if ($event->isPasteIntoDisabled())
		{
			return BackendBindings::generateImage('pasteafter_.gif', $strLabel, 'class="blink"');
		}

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$event->getHrefAfter(),
			specialchars($strLabel),
			'onclick="Backend.getScrollOffset()"',
			BackendBindings::generateImage('pasteafter.gif', $strLabel, 'class="blink"')
		);
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
			$arrButtons[$operation] = $this->buildOperation($objOperation, $objModelRow, $blnCircularReference, $arrChildRecordIds, $strPrevious, $strNext);
		}

		// Add paste into/after icons
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$objClipboard = $this->getEnvironment()->getClipboard();

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

			$buttonEvent = new GetPasteButtonEvent();
			$buttonEvent
				->setEnvironment($this->getEnvironment())
				->setModel($objModelRow)
				->setCircularReference(false)
				->setPrevious(null)
				->setNext(null)
				->setHrefAfter(BackendBindings::addToUrl($strAdd2UrlAfter))
				->setHrefInto(BackendBindings::addToUrl($strAdd2UrlInto))
				// Check if the id is in the ignore list.
				->setPasteAfterDisabled($objClipboard->isCut() && in_array($objModelRow->getID(), $objClipboard->getCircularIds()))
				->setPasteIntoDisabled($objClipboard->isCut() && in_array($objModelRow->getID(), $objClipboard->getCircularIds()));

			$this->dispatchEvent(GetPasteButtonEvent::NAME, $buttonEvent);

			$arrButtons['pasteafter'] = $this->renderPasteAfterButton($buttonEvent);
			if ($this->getDataDefinition()->getSortingMode() == 5)
			{
				$arrButtons['pasteinto'] = $this->renderPasteIntoButton($buttonEvent);
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
			throw new \RuntimeException('No panel information stored in data container.');
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
