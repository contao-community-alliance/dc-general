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

namespace DcGeneral\View\Widget;

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\View\ContaoBackendViewTemplate;
use DcGeneral\View\Widget\Event\ResolveWidgetErrorMessageEvent;

class ContaoWidgetManager implements WidgetManagerInterface
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * A list with all widgets
	 * @var array
	 */
	protected $arrWidgets = array();

	/**
	 * @param EnvironmentInterface           $environment
	 *
	 * @param \DcGeneral\Data\ModelInterface $model
	 */
	function __construct(EnvironmentInterface $environment, $model)
	{
		$this->environment = $environment;
		$this->model       = $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasWidget($property)
	{
		try
		{
			return $this->getWidget($property) !== null;
		}
		catch(\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get special labels
	 *
	 * @param PropertyInterface $propInfo
	 *
	 * @return string
	 */
	protected function getXLabel($propInfo)
	{
		$strXLabel = '';
		$environment = $this->getEnvironment();
		$defName     = $environment->getDataDefinition()->getName();
		$translator  = $environment->getTranslator();

		// Toggle line wrap (textarea)
		if ($propInfo->getWidgetType() === 'textarea' && !array_key_exists('rte', $propInfo->getExtra()))
		{
			$strXLabel .= ' ' . BackendBindings::generateImage(
					'wrap.gif',
					$translator->translate('wordWrap', 'MSC'),
					sprintf(
						'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"',
						specialchars($translator->translate('wordWrap', 'MSC')),
						$propInfo->getName()
					)
				);
		}

		// Add the help wizard
		if (array_key_exists('helpwizard', $propInfo->getExtra()))
		{
			$strXLabel .= sprintf(
				' <a href="contao/help.php?table=%s&amp;field=%s" title="%s" onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>',
				$defName,
				$propInfo->getName(),
				specialchars($translator->translate('helpWizard', 'MSC')),
				BackendBindings::generateImage(
					'about.gif',
					$translator->translate('helpWizard', 'MSC'),
					'style="vertical-align:text-bottom;"'
				)
			);
		}

		// Add the popup file manager
		if ($propInfo->getWidgetType() === 'fileTree')
		{
			// In Contao 3 it is always a file picker - no need for the button.
			if (version_compare(VERSION, '3.0', '<'))
			{
				$strXLabel .= sprintf(
					' <a href="contao/files.php" title="%s" onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">%s</a>',
					specialchars($translator->translate('fileManager', 'MSC')),
					BackendBindings::generateImage(
						'filemanager.gif', $translator->translate('fileManager', 'MSC'), 'style="vertical-align:text-bottom;"'
					)
				);
			}
		}
		// Add table import wizard
		else if ($propInfo->getWidgetType() === 'tableWizard')
		{
			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s',
				ampersand(BackendBindings::addToUrl('key=table')),
				specialchars($translator->translate('importTable/1', $defName)),
				BackendBindings::generateImage(
					'tablewizard.gif',
					$translator->translate('importTable/0', $defName),
					'style="vertical-align:text-bottom;"'
				),
				BackendBindings::generateImage(
					'demagnify.gif',
					$translator->translate('shrink/0', $defName),
					sprintf(
						'title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"',
						specialchars($translator->translate('shrink/1', $defName))
					)
				),
				BackendBindings::generateImage(
					'magnify.gif',
					$translator->translate('expand/0', $defName),
					sprintf(
						'title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"',
						specialchars($translator->translate('expand/1', $defName))
					)
				)
			);
		}
		// Add list import wizard
		else if ($propInfo->getWidgetType() === 'listWizard')
		{
			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
				ampersand(BackendBindings::addToUrl('key=list')),
				specialchars($translator->translate('importList/1', $defName)),
				BackendBindings::generateImage(
					'tablewizard.gif',
					$translator->translate('importList/0', $defName),
					'style="vertical-align:text-bottom;"'
				)
			);
		}

		return $strXLabel;
	}

	/**
	 * Return the widget for a given field.
	 *
	 * @param $fieldName
	 *
	 * @return \Widget
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function getWidget($fieldName)
	{

		// Load from cache
		if (isset($this->arrWidgets[$fieldName]))
		{
			return $this->arrWidgets[$fieldName];
		}

		// Check if editable
		if (!$this->isEditableField($fieldName))
		{
			return NULL;
		}

		// Get config and check it
		$arrConfig = $this->getFieldDefinition($fieldName);
		if (count($arrConfig) == 0)
		{
			return NULL;
		}

		$strInputName = $fieldName . '_' . $this->mixWidgetID;
		// FIXME: do we need to unset this again? do we need to set this elsewhere? load/save/wizard, all want to know this - centralize it
		$this->strField = $fieldName;
		$this->strInputName = $strInputName;

		/* $arrConfig['eval']['encrypt'] ? $this->Encryption->decrypt($this->objActiveRecord->$fieldName) : */
		$varValue = deserialize($this->getEnvironment()->getCurrentModel()->getProperty($fieldName));

		// Load Callback
		$mixedValue = $this->getEnvironment()->getCallbackHandler()->loadCallback($fieldName, $varValue);

		if (!is_null($mixedValue))
		{
			$varValue = $mixedValue;
		}

		$arrConfig['eval']['xlabel'] = $this->getXLabel($arrConfig);
		if (is_array($arrConfig['input_field_callback']))
		{
			$this->import($arrConfig['input_field_callback'][0]);
			$objWidget = $this->{$arrConfig['input_field_callback'][0]}->{$arrConfig['input_field_callback'][1]}($this, $arrConfig['eval']['xlabel']);
			return $this->arrWidgets[$fieldName] = isset($objWidget) ? $objWidget : '';
		}

		// ToDo: switch for BE / FE handling
		$strClass = $GLOBALS['BE_FFL'][$arrConfig['inputType']];
		if (!$this->classFileExists($strClass))
		{
			return $this->arrWidgets[$fieldName] = NULL;
		}

		// FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..)
		if (isset(self::$arrDates[$arrConfig['eval']['rgxp']])
			&& !$arrConfig['eval']['mandatory']
			&& is_numeric($varValue) && $varValue == 0)
		{
			$varValue = '';
		}

		// OH: why not $required = $mandatory always? source: DataContainer 226
		$arrConfig['eval']['required'] = $varValue == '' && $arrConfig['eval']['mandatory'] ? true : false;
		// OH: the whole prepareForWidget(..) thing is an only mess
		// widgets should parse the configuration by themselfs, depending on what they need

		if (version_compare(VERSION, '3.0', '>='))
		{
			$arrPrepared = \Widget::getAttributesFromDca($arrConfig, $strInputName, $varValue, $fieldName, $this->strTable, $this);
		}
		else
		{
			$arrPrepared = $this->prepareForWidget($arrConfig, $strInputName, $varValue, $fieldName, $this->strTable);
		}

		// Bugfix CS: ajax subpalettes are really broken.
		// Therefore we reset to the default checkbox behaviour here and submit the entire form.
		// This way, the javascript needed by the widget (wizards) will be correctly evaluated.
		if ($arrConfig['inputType'] == 'checkbox' && is_array($GLOBALS['TL_DCA'][$this->strTable]['subpalettes']) && in_array($fieldName, array_keys($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'])) && $arrConfig['eval']['submitOnChange'])
		{
			$arrPrepared['onclick'] = $arrConfig['eval']['submitOnChange'] ? "Backend.autoSubmit('".$this->strTable."')" : '';
		}
		//$arrConfig['options'] = $arrPrepared['options'];

		$objWidget = new $strClass($arrPrepared);
		// OH: what is this? source: DataContainer 232
		$objWidget->currentRecord = $this->intId;

		if ($objWidget instanceof \uploadable)
		{
			$this->blnUploadable = true;
		}

		// OH: xlabel, wizard: two ways to rome? wizards are the better way I think
		$objWidget->wizard = implode('', $this->getEnvironment()->getCallbackHandler()->executeCallbacks($arrConfig['wizard'], $this));

		return $this->arrWidgets[$fieldName] = $objWidget;
	}

	protected function buildDatePicker($objWidget)
	{
		$translator = $this->getEnvironment()->getTranslator();
		// TODO: need better interface to Contao Config class here.
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
			'startDay' => $translator->translate('weekOffset', 'MSC'),
			'days' => array_values((array)$translator->translate('DAYS', 'MSC')),
			'dayShort' => $translator->translate('dayShortLength', 'MSC'),
			'months' => array_values((array)$translator->translate('MONTHS', 'MSC')),
			'monthShort' => $translator->translate('monthShortLength', 'MSC')
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

		if (version_compare(DATEPICKER, '2.1','>'))
		{
			return 'new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
				draggable:false,
				toggle:$$("#toggle_' . $objWidget->id . '"),
				format:"' . \Date::formatToJs($strFormat) . '",
				positionOffset:{x:-197,y:-182}' . $time . ',
				pickerClass:"datepicker_dashboard",
				useFadeInOut:!Browser.ie,
				startDay:' . $translator->translate('weekOffset', 'MSC') . ',
				titleFormat:"' . $translator->translate('titleFormat', 'MSC') . '"
			});';
		}

		return 'new DatePicker(' . json_encode('#ctrl_' . $objWidget->id) . ', ' . json_encode($arrConfig) . ');';
	}

	/**
	 * Generate the help msg for a property.
	 *
	 * @param string $property The name of the property
	 *
	 * @return string
	 */
	protected function generateHelpText($property)
	{
		$environment = $this->getEnvironment();
		$defName     = $environment->getDataDefinition()->getName();
		$propInfo    = $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($property);
		$label       = $propInfo->getLabel();
		$widgetType  = $propInfo->getWidgetType();

		// TODO: need better interface to Contao Config class here.
		if (!$GLOBALS['TL_CONFIG']['showHelp'] || $widgetType == 'password' || !strlen($label))
		{
			return '';
		}

		return '<p class="tl_help tl_tip">' . $label . '</p>';
	}

	/**
	 * Process all values from the PropertyValueBag through the widgets.
	 *
	 * @param PropertyValueBag $input
	 */
	public function processInput(PropertyValueBag $propertyValues)
	{

	}

	/**
	 * Process all errors from the PropertyValueBag and add them to the widgets.
	 *
	 * @param PropertyValueBag $input
	 */
	public function processErrors(PropertyValueBag $propertyValues)
	{
		$propertyErrors = $propertyValues->getInvalidPropertyErrors();

		if ($propertyErrors) {
			global $container;
			/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
			$dispatcher       = $container['event-dispatcher'];

			foreach ($propertyErrors as $property => $errors)
			{
				$widget = $this->getWidget($property);

				foreach ($errors as $error) {
					$event = new ResolveWidgetErrorMessage($error);
					$dispatcher->dispatch($event::NAME, $event);

					$widget->addError($event->getError());
				}
			}
		}
	}

	/**
	 * Lookup buffer for processInput()
	 *
	 * holds the values of all already processed input fields.
	 *
	 * @var array
	 */
	protected $arrProcessed = array();

	/**
	 * Lookup buffer for processInput()
	 *
	 * Holds the names of all already processed input fields.
	 *
	 * @var array
	 */
	protected $arrProcessedNames = array();

	/**
	 * Parse|Check|Validate each field and save it.
	 *
	 * @param string $fieldName Name of current field
	 *
	 * @return mixed
	 */
	protected function processPropertyInput($fieldName)
	{
		if (in_array($fieldName, $this->arrProcessedNames))
		{
			return $this->arrProcessed[$fieldName];
		}

		$this->arrProcessedNames[] = $fieldName;
		$strInputName = $fieldName . '_' . $this->mixWidgetID;

		// Return if no submit, field is not editable or not in input
		if (!(
			$this->blnSubmitted &&
			isset($this->arrInputs[$strInputName]) &&
			$this->isEditableField($fieldName) && !(
				isset($this->arrDCA['fields'][$fieldName]['eval']['readonly']) &&
				$this->arrDCA['fields'][$fieldName]['eval']['readonly']
			)
		))
		{
			return $this->arrProcessed[$fieldName] = null;
		}

		// Build widget
		$objWidget = $this->getWidget($fieldName);
		if (!($objWidget instanceof \Widget))
		{
			return $this->arrProcessed[$fieldName] = null;
		}

		// Validate
		$objWidget->validate();
		// TODO: dependency injection
		if (\Input::getInstance()->post('SUBMIT_TYPE') == 'auto')
		{
			// HACK: we would need a Widget::clearErrors() here but something like this does not exist, hence we have a class that does this for us.
			WidgetAccessor::resetErrors($objWidget);
		}

		// Check
		if ($objWidget->hasErrors())
		{
			$this->blnNoReload = true;
			return $this->arrProcessed[$fieldName] = null;
		}

		if (!$objWidget->submitInput())
		{
			return $this->arrProcessed[$fieldName] = $this->getEnvironment()->getCurrentModel()->getProperty($fieldName);
		}

		// Get value and config
		$varNew = $objWidget->value;
		$arrConfig = $this->getFieldDefinition($fieldName);

		// If array sort
		if (is_array($varNew))
		{
			ksort($varNew);
		}
		// if field has regex from type date, formate the value to date
		else if ($varNew != '' && isset(self::$arrDates[$arrConfig['eval']['rgxp']]))
		{ // OH: this should be a widget feature
			$objDate = new Date($varNew, $GLOBALS['TL_CONFIG'][$arrConfig['eval']['rgxp'] . 'Format']);
			$varNew = $objDate->tstamp;
		}

		$this->import('Input');

		//Handle multi-select fields in "override all" mode
		// OH: this should be a widget feature
		if (($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard') && $arrConfig['eval']['multiple'] && $this->Input->get('act') == 'overrideAll')
		{
			if ($arrNew == null || !is_array($arrNew))
			{
				$arrNew = array();
			}

			// FIXME: this will NOT work, as it still uses activeRecord - otoh, what is this intended for? wizards?
			switch ($this->Input->post($objWidget->name . '_update'))
			{
				case 'add':
					$varNew = array_values(array_unique(array_merge(deserialize($this->objActiveRecord->$fieldName, true), $arrNew)));
					break;

				case 'remove':
					$varNew = array_values(array_diff(deserialize($this->objActiveRecord->$fieldName, true), $arrNew));
					break;

				case 'replace':
					$varNew = $arrNew;
					break;
			}

			if (!$varNew)
			{
				$varNew = '';
			}
		}

		// Call the save callbacks
		try
		{
			$varNew = $this->getEnvironment()->getCallbackHandler()->saveCallback($arrConfig, $varNew);
		}
		catch (\Exception $e)
		{
			$this->blnNoReload = true;
			$objWidget->addError($e->getMessage());
			return $this->arrProcessed[$fieldName] = null;
		}

		// Check on value empty
		if ($varNew == '' && $arrConfig['eval']['doNotSaveEmpty'])
		{
			$this->blnNoReload = true;
			$objWidget->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
			return $this->arrProcessed[$fieldName] = null;
		}

		if ($varNew != '')
		{
			if ($arrConfig['eval']['encrypt'])
			{
				$varNew = $this->Encryption->encrypt(is_array($varNew) ? serialize($varNew) : $varNew);
			}
			else if ($arrConfig['eval']['unique'] && !$this->getDataProvider($this->getEnvironment()->getCurrentModel()->getProviderName())->isUniqueValue($fieldName, $varNew, $this->getEnvironment()->getCurrentModel()->getID()))
			{
				$this->blnNoReload = true;
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $objWidget->label));
				return $this->arrProcessed[$fieldName] = null;
			}
			else if ($arrConfig['eval']['fallback'])
			{
				$this->getDataProvider($this->getEnvironment()->getCurrentModel()->getProviderName())->resetFallback($fieldName);
			}
		}

		$this->arrProcessed[$fieldName] = $varNew;

		return $varNew;
	}
}
