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

use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataContainerInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\View\Widget\Events\ResolveWidgetErrorMessage;

class ContaoWidgetManager implements WidgetManagerInterface
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * A list with all widgets
	 * @var array
	 */
	protected $arrWidgets = array();

	function __construct(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Check if the given field has a widget.
	 *
	 * @param $fieldName
	 *
	 * @return bool
	 */
	public function hasWidget($fieldName)
	{

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
