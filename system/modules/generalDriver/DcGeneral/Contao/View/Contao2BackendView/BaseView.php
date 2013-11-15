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

namespace DcGeneral\Contao\View\Contao2BackendView;

use DcGeneral\Contao\View\Contao2BackendView\Event\EditModelBeforeSaveEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\Panel\LimitElementInterface;
use DcGeneral\Panel\PanelContainerInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\SubmitElementInterface;
use DcGeneral\View\ContaoBackendViewTemplate;
use DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use DcGeneral\View\Widget\ContaoWidgetManager;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use DcGeneral\Contao\BackendBindings;
use DcGeneral\DataContainerInterface;

class BaseView implements BackendViewInterface
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

	// Overall Vars ---------------------------------
	protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.</div>";

	// Objects --------------------------------------

	/**
	 * Driver class
	 * @var DataContainerInterface
	 *
	 * @deprecated
	 */
	protected $objDC = null;

	/**
	 * Used by palette rendering.
	 *
	 * @var array
	 */
	protected $arrStack = array();

	/**
	 * The attached environment.
	 *
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * @var PanelContainerInterface
	 */
	protected $panel;

	/**
	 * @return DataContainerInterface
	 *
	 * @deprecated
	 */
	public function getDC()
	{
		return $this->objDC;
	}

	/**
	 * @param DataContainerInterface $objDC
	 *
	 * @deprecated
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
		$this->setEnvironment($objDC->getEnvironment());
	}

	/**
	 * Dispatch an event to the dispatcher.
	 *
	 * The event will first get triggered with the name of the active data provider within square brackets appended
	 * and plain afterwards.
	 *
	 * Example:
	 *   Event name: "some-event"
	 *   DP name:    "tl_table"
	 *
	 *   1. dispatch: "some-event[tl_table]"
	 *   2. dispatch: "some-event"
	 *
	 * @param string                                   $eventName
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event
	 * @deprecated Use $this->getEnvironment()->getEventPropagator()->propagate() instead.
	 */
	protected function dispatchEvent($eventName, $event)
	{
		$this->getEnvironment()->getEventPropagator()->propagate($event, array($this->getEnvironment()->getDataDefinition()->getName()));
	}

	public function setEnvironment(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return \DcGeneral\EnvironmentInterface
	 */
	public function getEnvironment()
	{
		return $this->environment;
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

	protected function translate($path, $section = null)
	{
		return $this->getEnvironment()->getTranslator()->translate($path, $section);
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $template
	 *
	 * @return BaseView
	 */
	protected function addToTemplate($name, $value, $template)
	{
		$template->$name = $value;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPanel($panelContainer)
	{
		$this->panel = $panelContainer;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPanel()
	{
		return $this->panel;
	}

	/**
	 * Retrieve the view section for this view.
	 *
	 * @return Contao2BackendViewDefinitionInterface
	 */
	protected function getViewSection()
	{
		return $this->getDataDefinition()->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
	}

	/**
	 * Determines if this view is opened in a popup frame.
	 *
	 * @return bool
	 */
	protected function isPopup()
	{
		return \Input::getInstance()->get('popup');
	}

	protected function isSelectModeActive()
	{
		return \Input::getInstance()->get('act') == 'select';
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
		$property   = $this->getDataDefinition()->getPropertiesSection()->getProperty($field);

		// No property? Get out!
		if (!$property)
		{
			return '-';
		}

		$evaluation = $property->getExtra();
		$remoteNew  = '';

		if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple'])
		{
			$remoteNew = ($value != '') ? ucfirst($this->translate('yes', 'MSC')) : ucfirst($this->translate('no', 'MSC'));
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

			if ($month = $this->translate($intMonth, 'MONTHS'))
			{
				$remoteNew = ($value != '') ? $month . ' ' . date('Y', $value) : '-';
			}
		}
		elseif (in_array($mode, array(9, 10)))
		{
			$remoteNew = ($value != '') ? date('Y', $value) : '-';
		}
		else
		{
			if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple'])
			{
				$remoteNew = ($value != '') ? $field : '';
			}
			elseif (is_array($property->get('reference')))
			{
				$reference = $property->get('reference');
				$remoteNew = $reference[$value];
			}
			elseif (array_is_assoc($property->getOptions()))
			{
				$options   = $property->getOptions();
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
		static $lookup = array();

		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();
		$property    = $definition->getPropertiesSection()->getProperty($field);
		$options     = $property->getOptions();
		$reference   = $property->get('reference');

		if (array_is_assoc($options))
		{
			$group = $options[$value];
		}
		elseif (isset($reference[$value]))
		{
			$group = is_array($reference[$value]) ? $reference[$value][0] : $reference[$value];
		}
		else
		{
			if (!isset($lookup[$field]))
			{
				$event = new GetPropertyOptionsEvent($environment);
				$event->setFieldName($field);

				$this->dispatchEvent(GetPropertyOptionsEvent::NAME, $event);

				$lookup[$field] = $event->getOptions();
			}

			$group = $lookup[$field][$value];
		}

		// FIXME: What is this undocumented feature?
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

		$event = new GetGroupHeaderEvent($environment);

		$event
			->setModel($objModelRow)
			->setGroupField($group)
			->setSortingMode($mode)
			->setValue($field);

		$this->dispatchEvent(GetGroupHeaderEvent::NAME, $event);

		$group = $event->getGroupField();

		return $group;
	}


	protected function getButtonLabel($strButton)
	{
		$definition = $this->getEnvironment()->getDataDefinition();
		if (($label = $this->translate($strButton, $definition->getName())) !== $strButton)
		{
			return $label;
		}
		else if (($label = $this->translate($strButton, 'MSC')) !== $strButton)
		{
			return $label;
		}
		// Fallback, just return the key as is it.
		else
		{
			return $strButton;
		}
	}

	/**
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array()
	 */
	protected function getEditButtons()
	{
		$buttons         = array();
		$definition      = $this->getEnvironment()->getDataDefinition();
		$basicDefinition = $definition->getBasicDefinition();

		// TODO: we have hardcoded html in here, is this really the best idea?

		$buttons['save'] = sprintf(
			'<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="%s" />',
			$this->getButtonLabel('save')
		);

		$buttons['saveNclose'] = sprintf(
			'<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="%s" />',
			$this->getButtonLabel('saveNclose')
		);

		if (!($this->isPopup() || $basicDefinition->isClosed()) && $basicDefinition->isCreatable())
		{
			$buttons['saveNcreate'] = sprintf(
				'<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="%s" />',
				$this->getButtonLabel('saveNcreate')
			);
		}

		// TODO: unknown input param s2e - I guess it means "switch to edit" but from which view used?
		if (\Input::get('s2e'))
		{
			$buttons['saveNedit'] = sprintf(
				'<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="%s" />',
				$this->getButtonLabel('saveNedit')
			);
		}
		elseif (
			!$this->isPopup()
			&& (($basicDefinition->getMode() == BasicDefinitionInterface::MODE_PARENTEDLIST)
				|| strlen($basicDefinition->getParentDataProvider())
				|| $basicDefinition->isSwitchToEditEnabled()
			)
		)
		{
			$buttons['saveNback'] = sprintf(
				'<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="%s" />',
				$this->getButtonLabel('saveNback')
			);
		}

		$event = new GetEditModeButtonsEvent($this->getEnvironment());
		$event->setButtons($buttons);

		$this->getEnvironment()->getEventPropagator()->propagate($event, array($definition->getName()));

		return $event->getButtons();
	}

	/**
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array()
	 */
	protected function getSelectButtons()
	{
		$definition = $this->getEnvironment()->getDataDefinition();
		$buttons    = array();

		// TODO: we have hardcoded html in here, is this really the best idea?

		if (false) // TODO refactore $definition->isDeletable())
		{
			$buttons['delete'] = sprintf(
				'<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'%s\')" value="%s">',
				$GLOBALS['TL_LANG']['MSC']['delAllConfirm'],
				specialchars($this->translate('deleteSelected', 'MSC'))
			);
		}

		// TODO: strictly spoken, cut is editing - should we wrap this within if ($definition->isEditable()) here?
		$buttons['cut'] = sprintf(
			'<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="%s">',
			specialchars($this->translate('moveSelected', 'MSC'))
		);

		$buttons['copy'] = sprintf(
			'<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="%s">',
			specialchars($this->translate('copySelected', 'MSC'))
		);

		if (true) // TODO refactore $definition->isEditable())
		{
			$buttons['override'] = sprintf(
				'<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="%s">',
				specialchars($this->translate('overrideSelected', 'MSC'))
			);

			$buttons['edit'] = sprintf(
				'<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="%s">',
				specialchars($this->translate('editSelected', 'MSC'))
			);
		}
		/**
		$buttons[''] = sprintf(
		'',
		specialchars($GLOBALS['TL_LANG']['MSC'][''])
		);
		 */

		$event = new GetSelectModeButtonsEvent($this->getEnvironment());
		$event->setButtons($buttons);

		$this->dispatchEvent(GetSelectModeButtonsEvent::NAME, $event);

		return $event->getButtons();
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

	/**
	 * Update the clipboard in the Environment with data from the InputProvider.
	 *
	 * The following parameters have to be provided by the input provider:
	 *
	 * Name      Type   Description
	 * clipboard bool   Flag determining if the clipboard shall get cleared.
	 * act       string Action to perform, either paste, cut or create.
	 * id        mixed  The Id of the item to copy. In mode cut this is the id of the item to be moved.

	 * @return BaseView
	 */
	public function checkClipboard()
	{
		$objInput     = $this->getEnvironment()->getInputProvider();
		$objClipboard = $this->getEnvironment()->getClipboard();

		// Reset Clipboard
		if ($objInput->getParameter('clipboard') == '1')
		{
			$objClipboard->clear();
		}
		// Push some entry into clipboard.
		elseif ($objInput->getParameter('act') == 'paste')
		{
			$objDataProv  = $this->getEnvironment()->getDataDriver();
			$id           = $objInput->getParameter('id');

			if ($objInput->getParameter('mode') == 'cut')
			{
				$arrIgnored = array($id);

				$objModel = $objDataProv->fetch($objDataProv->getEmptyConfig()->setId($id));

				// We have to ignore all children of this element in mode 5 (to prevent circular references).
				if ($this->getDataDefinition()->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL)
				{
					$arrIgnored = $this->getEnvironment()->getController()->assembleAllChildrenFrom($objModel);
				}

				$objClipboard
					->clear()
					->cut($id)
					->setCircularIds($arrIgnored);
			}
			elseif ($objInput->getParameter('mode') == 'create')
			{
				$arrIgnored     = array($id);
				$objContainedId = trimsplit(',', $objInput->getParameter('childs'));

				$objClipboard
					->clear()
					->create($id)
					->setCircularIds($arrIgnored);

				if (is_array($objContainedId) && !empty($objContainedId))
				{
					$objClipboard->setContainedIds($objContainedId);
				}
			}
		}
		// Check clipboard from session.
		else
		{
			$objClipboard->loadFrom($this->getEnvironment());
		}

		// Let the clipboard save it's values persistent.
		$objClipboard->saveTo($this->getEnvironment());

		return $this;
	}

	protected function isMultiLanguage($mixId)
	{
		return count($this->getEnvironment()->getController()->getSupportedLanguages($mixId)) > 0;
	}

	/**
	 * Check if the data provider is multi language.
	 * Save the current language and language array.
	 *
	 * @return void
	 */
	protected function checkLanguage()
	{
		$environment     = $this->getEnvironment();
		$inputProvider   = $environment->getInputProvider();
		$objDataProvider = $environment->getDataProvider();
		$strProviderName = $environment->getDataDefinition()->getName();
		$mixID           = $environment->getInputProvider()->getParameter('id');
		$arrLanguage     = $environment->getController()->getSupportedLanguages($mixID);

		if (!$arrLanguage)
		{
			return;
		}

		// Load language from Session
		$arrSession = $inputProvider->getPersistentValue('dc_general');
		if (!is_array($arrSession))
		{
			$arrSession = array();
		}
		/** @var \DcGeneral\Data\MultiLanguageDriverInterface $objDataProvider */

		// try to get the language from session
		if (isset($arrSession["ml_support"][$strProviderName][$mixID]))
		{
			$strCurrentLanguage = $arrSession["ml_support"][$strProviderName][$mixID];
		}
		else
		{
			$strCurrentLanguage = $GLOBALS['TL_LANGUAGE'];
		}

		// Get/Check the new language
		if (strlen($inputProvider->getValue('language')) != 0 && $inputProvider->getValue('FORM_SUBMIT') == 'language_switch')
		{
			if (array_key_exists($inputProvider->getValue('language'), $arrLanguage))
			{
				$strCurrentLanguage = $inputProvider->getValue('language');
			}
		}

		if (!array_key_exists($strCurrentLanguage, $arrLanguage))
		{
			$strCurrentLanguage  = $objDataProvider->getFallbackLanguage($mixID)->getID();
		}

		$arrSession['ml_support'][$strProviderName][$mixID] = $strCurrentLanguage;
		$inputProvider->setPersistentValue('dc_general', $arrSession);

		$objDataProvider->setCurrentLanguage($strCurrentLanguage);
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
	 * @return string
	 */
	public function copy()
	{
		return vsprintf($this->notImplMsg, 'copy - Mode');
	}

	/**
	 * @todo All
	 * @return string
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
	 * @return string
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
	 * @return string
	 */
	public function cutAll()
	{
		return vsprintf($this->notImplMsg, 'cutAll - Mode');
	}

	/**
	 * @todo All
	 * @return string
	 */
	public function delete()
	{
		return vsprintf($this->notImplMsg, 'delete - Mode');
	}

	/**
	 * @todo All
	 * @return string
	 */
	public function move()
	{
		return vsprintf($this->notImplMsg, 'move - Mode');
	}

	/**
	 * @todo All
	 * @return string
	 */
	public function undo()
	{
		return vsprintf($this->notImplMsg, 'undo - Mode');
	}

	protected function checkRestoreVersion()
	{
		$environment             = $this->getEnvironment();
		$definition              = $environment->getDataDefinition();
		$dataProvider            = $environment->getDataProvider();
		$dataProviderInformation = $definition->getDataProviderDefinition()->getInformation($definition->getBasicDefinition()->getDataProvider());
		$inputProvider           = $environment->getInputProvider();
		$modelId                 = $environment->getInputProvider()->getParameter('id');

		if ($dataProviderInformation->isVersioningEnabled()
			&& ($inputProvider->getValue('FORM_SUBMIT') === 'tl_version')
			&& ($modelVersion = $inputProvider->getValue('version')) !== null)
		{
			$model = $dataProvider->getVersion($modelId, $modelVersion);

			if ($model === null)
			{
				$message = sprintf('Could not load version %s of record ID %s from %s', $modelVersion, $modelId, $definition->getBasicDefinition()->getDataProvider());
				BackendBindings::log($message, TL_ERROR, 'DC_General - edit()');
				throw new DcGeneralRuntimeException($message);
			}

			$dataProvider->save($model);
			$dataProvider->setVersionActive($modelId, $modelVersion);
			BackendBindings::reload();
		}
	}

	/**
	 * Generate the view for edit
	 *
	 * @return string
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 */
	public function edit()
	{
		// Load basic information
		$this->checkLanguage();

		$environment             = $this->getEnvironment();
		$definition              = $environment->getDataDefinition();
		$dataProvider            = $environment->getDataProvider();
		$dataProviderInformation = $definition->getDataProviderDefinition()->getInformation($definition->getBasicDefinition()->getDataProvider());
		$inputProvider           = $environment->getInputProvider();
		$palettesDefinition      = $definition->getPalettesDefinition();
		$modelId                 = $inputProvider->getParameter('id');
		$propertyDefinitions     = $definition->getPropertiesDefinition();
		$blnSubmitted            = ($inputProvider->getValue('FORM_SUBMIT') === $definition->getName());

		$this->checkRestoreVersion();

		$model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId));
		$widgetManager = new ContaoWidgetManager($environment, $model);

		// Check if table is editable
		if (!$definition->getBasicDefinition()->isEditable())
		{
			$message = 'DataContainer ' . $definition->getName() . ' is not editable';
			BackendBindings::log($message, TL_ERROR, 'DC_General - edit()');
			throw new DcGeneralRuntimeException($message);
		}

		// Check if table is closed but we are adding a new item.
		if ((!$modelId) && $definition->getBasicDefinition()->isClosed())
		{
			$message = 'DataContainer ' . $definition->getName() . ' is closed';
			BackendBindings::log($message, TL_ERROR, 'DC_General - edit()');
			throw new DcGeneralRuntimeException($message);
		}

		// Pass 1: Get the palette for the values stored in the model.
		$palette = $palettesDefinition->findPalette($model);

		$propertyValues     = $this->processInput($widgetManager);
		$errors             = array();
		if ($blnSubmitted && $propertyValues)
		{
			// Pass 2: Determine the real palette we want to work on if we have some data submitted.
			$palette = $palettesDefinition->findPalette($model, $propertyValues);

			// Update the model - the model might add some more errors to the propertyValueBag via exceptions.
			$this->getEnvironment()->getController()->updateModelFromPropertyBag($model, $propertyValues);
		}

		$arrFieldSets = array();
		foreach ($palette->getLegends() as $legend)
		{
			$legendName = $environment->getTranslator()->translate($legend->getName() . '_legend', $definition->getName());
			$fields     = array();
			foreach($legend->getProperties($model, $propertyValues) as $property)
			{
				if (!$propertyDefinitions->hasProperty($property->getName()))
				{
					throw new DcGeneralInvalidArgumentException('Property ' . $property->getName() . ' is mentioned in palette but not defined in propertyDefinition.');
				}

				// if this property is invalid, fetch the error.
				if ($propertyValues && $propertyValues->hasPropertyValue($property->getName()) && $propertyValues->isPropertyValueInvalid($property->getName()))
				{
					$errors = array_merge($errors, $propertyValues->getPropertyValueErrors($property));
				}

				$fields[] = $widgetManager->renderWidget($property->getName());
			}
			$arrFieldSet['label']   = $legendName;
			$arrFieldSet['class']   = 'tl_box';
			$arrFieldSet['palette'] = implode('', $fields);
			$arrFieldSet['legend']  = $legend->getName();

			$arrFieldSets[] = $arrFieldSet;
		}

		if ($blnSubmitted)
		{
			$event = new EditModelBeforeSaveEvent($environment, $model);
			$environment->getEventPropagator()->propagate($event, array(
				$this->getEnvironment()->getDataDefinition()->getName(),
			));

			if ($model->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$dataProvider->save($model);

				if ($dataProviderInformation->isVersioningEnabled())
				{
					// Compare version and current record
					$currentVersion = $dataProvider->getActiveVersion($modelId);
					if (!$currentVersion || !$dataProvider->sameModels($model, $dataProvider->getVersion($modelId, $currentVersion)))
					{
						// TODO: FE|BE switch
						$user = \BackendUser::getInstance();
						$dataProvider->saveVersion($model, $user->username);
					}
				}
			}

			BackendBindings::reload();
		}

		if ($model->getId())
		{
			$strHeadline = sprintf($this->translate('editRecord', $definition->getName()), 'ID ' . $model->getId());
			if ($strHeadline === 'editRecord')
			{
				$strHeadline = sprintf($this->translate('editRecord', 'MSC'), 'ID ' . $model->getId());
			}
		}
		else
		{
			// TODO: new language string for "new" model?
			$strHeadline = sprintf($this->translate('editRecord', 'MSC'), '');
		}

		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_edit');
		$objTemplate->setData(array(
			'fieldsets' => $arrFieldSets,
			'versions' => $dataProviderInformation->isVersioningEnabled() ? $dataProvider->getVersions($model->getId()) : null,
			'subHeadline' => $strHeadline,
			'table' => $definition->getName(),
			'enctype' => 'multipart/form-data',
			'error' => $errors,
			'editButtons' => $this->getEditButtons(),
			'noReload' => (bool) $errors
		));

		if ($this->isMultiLanguage($model->getId()))
		{
			$langsNative = array();
			include(TL_ROOT . '/system/config/languages.php');

			/** @var MultiLanguageDriverInterface $dataProvider */
			$this
				->addToTemplate('languages', $environment->getController()->getSupportedLanguages($model->getId()), $objTemplate)
				->addToTemplate('language', $dataProvider->getCurrentLanguage(), $objTemplate)
				->addToTemplate('languageHeadline', $langsNative[$dataProvider->getCurrentLanguage()], $objTemplate);
		}
		else
		{
			$this
				->addToTemplate('languages', null, $objTemplate)
				->addToTemplate('languageHeadline', '', $objTemplate);
		}

		return $objTemplate->parse();
	}

	/**
	 * Show Informations about a data set
	 *
	 * @return String
	 */
	public function show()
	{
		// Load check multi language
		$environment  = $this->getEnvironment();
		$definition   = $environment->getDataDefinition();
		$properties   = $definition->getPropertiesDefinition();
		$dataProvider = $environment->getDataProvider();
		$mixId        = $environment->getInputProvider()->getParameter('id');

		// Check
		$this->checkLanguage();

		// Load record from data provider
		$objDBModel = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($mixId));

		if ($objDBModel == null)
		{
			$this->log('Could not find ID ' . $mixId . ' in Table ' . $definition->getName() . '.', 'DC_General show()', TL_ERROR);
			$this->redirect('contao/main.php?act=error'); // TODO refactore
		}

		$environment->setCurrentModel($objDBModel);

		// Load basic information.
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

		// Get allowed fields from dca
		if ($properties->getPropertyNames())
		{
			$allowedFields = array_unique(array_merge($allowedFields, $properties->getPropertyNames()));
		}

		$fields = array_intersect($allowedFields, $fields);

		// Show all allowed fields
		foreach ($fields as $strFieldName)
		{
			$property = $properties->getProperty($strFieldName);
			// TODO: we should examine the palette here and hide irrelevant fields.
			if ($property)
			{
				$extra = $property->getExtra();

				if (!in_array($strFieldName, $allowedFields)
					|| $property->getWidgetType() == 'password'
					|| ($extra && (
							array_key_exists('doNotShow', $extra)
							|| array_key_exists('hideInput', $extra))))
				{
					continue;
				}

				$label = $property->getLabel();

				$label = $this->getEnvironment()->getTranslator()->translate($label, $definition->getName());
			}
			else
			{
				$label = '';
			}

			$value = $this->getCurrentModel()->getProperty($strFieldName);
			$value = deserialize($value);

			// Make it human readable
			$arrFieldValues[$strFieldName] = $this->getReadableFieldValue($property, $objDBModel, $value);

			// Label
			if (count($label))
			{
				$arrFieldLabels[$strFieldName] = is_array($label) ? $label[0] : $label;
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
		$this
			->addToTemplate('headline', sprintf($this->translate('showRecord', 'MSC'), ($objDBModel->getId() ? 'ID ' . $objDBModel->getId() : '')), $objTemplate)
			->addToTemplate('arrFields', $arrFieldValues, $objTemplate)
			->addToTemplate('arrLabels', $arrFieldLabels, $objTemplate);

		if ($this->isMultiLanguage($objDBModel->getId()))
		{
			$this->addToTemplate('language', $environment->getController()->getSupportedLanguages($objDBModel->getId()), $objTemplate);
		}
		else
		{
			$this->addToTemplate('language', null, $objTemplate);
		}

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

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Sub Views
	 * Helper functions for the main views
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * Generates a subpalette for the given selector (field name)
	 *
	 * @param string $strSelector the name of the selector field.
	 *
	 * @return string the generated HTML code.
	 */
	public function generateAjaxPalette($strSelector)
	{
		return is_array($this->arrAjaxPalettes[$strSelector]) ? $this->generatePalette($this->arrAjaxPalettes[$strSelector], 'dcbe_general_field') : '';
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
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$listingConfig    = $this->getViewSection()->getListingConfig();
		$providerName     = $environment->getDataDefinition()->getName();
		$arrReturn        = array();
		$globalOperations = $this->getViewSection()->getGlobalCommands();


		if (!is_array($globalOperations))
		{
			$globalOperations = array();
		}

		// Make Urls absolute.
		foreach ($globalOperations as $k => $v)
		{
			$globalOperations[$k]['href'] = BackendBindings::addToUrl($v['href']);
		}

		// Special case - if select mode active, we must not display the "edit all" button.
		if ($this->isSelectModeActive())
		{
			unset($globalOperations['all']);
		}
		// We have the select mode
		else
		{
			$addButton = false;
			$strHref   = '';

			$viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
			$basicDefinition = $definition->getBasicDefinition();
			/** @var Contao2BackendViewDefinitionInterface $viewDefinition */
			$listingConfig = $viewDefinition->getListingConfig();
			$dataProviderDefinition = $definition->getDataProviderDefinition();

			// Add Buttons for mode x
			switch ($basicDefinition->getMode())
			{
				case BasicDefinitionInterface::MODE_FLAT:
					// Add new button
					$strHref = '';
					if (strlen($basicDefinition->getParentDataProvider()))
					{
						if ($listingConfig->getSortingMode() < 4)
						{
							$strHref = '&amp;mode=2';
						}
						$strHref = BackendBindings::addToUrl($strHref . '&amp;id=&amp;act=create&amp;pid=' . $this->getDC()->getId());
					}
					else
					{
						$strHref = BackendBindings::addToUrl('act=create');
					}

					$addButton = true; // TODO refactore !$basicDefinition->isClosed();
					break;

				case BasicDefinitionInterface::MODE_HIERARCHICAL:
				case BasicDefinitionInterface::MODE_PARENTEDLIST:
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
							'title'      => $this->translate('new/1', $providerName),
							'label'      => $this->translate('new/0', $providerName)
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
						'title'      => $this->translate('clearClipboard', 'MSC'),
						'label'      => $this->translate('clearClipboard', 'MSC')
					)
				)
				, $globalOperations
			);
		}

		// Add back button
		if ($this->isSelectModeActive() || $basicDefinition->getParentDataProvider())
		{
			$globalOperations = array_merge(
				array(
					'back_button'    => array
					(
						'class'      => 'header_back',
						'accesskey'  => 'b',
						'href'       => BackendBindings::getReferer(true, $this->getEnvironment()->getDataDefinition()->getParentDriverName()),
						'attributes' => 'onclick="Backend.getScrollOffset();"',
						'title'      => $this->translate('backBT', 'MSC'),
						'label'      => $this->translate('backBT', 'MSC')
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

			$buttonEvent = new GetGlobalButtonEvent($this->getEnvironment());
			$buttonEvent
				->setAccessKey($accessKey)
				->setAttributes($attributes)
				->setClass($v['class'])
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

		$buttonsEvent = new GetGlobalButtonsEvent($this->getEnvironment());
		$buttonsEvent
			->setButtons($arrReturn);
		$this->dispatchEvent(GetGlobalButtonsEvent::NAME, $buttonsEvent);

		return '<div id="' . $strButtonId . '">' . implode(' &nbsp; :: &nbsp; ', $buttonsEvent->getButtons()) . '</div>';
	}

	/**
	 * @param \DcGeneral\DataDefinition\Definition\View\CommandInterface $objCommand
	 *
	 * @param \DcGeneral\Data\ModelInterface                             $objModel
	 *
	 * @param bool                                                       $blnCircularReference
	 *
	 * @param array                                                      $arrChildRecordIds
	 *
	 * @param string                                                     $strPrevious
	 *
	 * @param string                                                     $strNext
	 *
	 * @return string
	 */
	protected function buildCommand($objCommand, $objModel, $blnCircularReference, $arrChildRecordIds, $strPrevious, $strNext)
	{
		// Set basic information.
		$opLabel = $objCommand->getLabel();
		if (strlen($opLabel))
		{
			$label = $opLabel;
		}
		else
		{
			$label = $objCommand->getName();
		}

		$opDesc = $objCommand->getDescription();
		if (strlen($opDesc))
		{
			$title = sprintf($opDesc, $objModel->getID());;
		}
		else
		{
			$title = sprintf('%s id %s', $label, $objModel->getID());
		}

		$strAttributes = $objCommand->getExtra()['attributes'];
		$attributes    = '';
		if (strlen($strAttributes))
		{
			$attributes = ltrim(sprintf($strAttributes, $objModel->getID()));
		}

		$arrParameters = (array) $objCommand->getParameters();

		// Cut needs some special information.
		if ($objCommand->getName() == 'cut')
		{
			// Get data provider from current and parent
			$strCDP = $objModel->getProviderName();
			$strPDP = $objModel->getMeta(DCGE::MODEL_PTABLE);


			$arrParameters['cdp'] = $strCDP;

			// Add parent provider if exists.
			if ($strPDP != null)
			{
				$arrParameters['pdp'] = $strPDP;
			}

			// If we have a id add it, used for mode 4 and all parent -> current views
			if ($this->getEnvironment()->getInputProvider()->hasParameter('id'))
			{
				$arrParameters['id'] = $this->getEnvironment()->getInputProvider()->getParameter('id');
			}

			// Source is the id of the element which should move
			$arrParameters['source'] = $objModel->getID();
		}
		else
		{
			// TODO: Shall we interface this option?
			$idParam = $objCommand->getExtra()['idparam'];
			if ($idParam)
			{
				$arrParameters['id'] = '';
				$arrParameters[$idParam] = $objModel->getID();
			}
			else
			{
				$arrParameters['id'] = $objModel->getID();
			}
		}

		$strHref = '';
		foreach ($arrParameters as $key => $value)
		{
			$strHref .= sprintf('&amp;%s=%s', $key, $value);
		}
		$strHref = BackendBindings::addToUrl($strHref);


		$buttonEvent = new GetOperationButtonEvent($this->getEnvironment());
		$buttonEvent
			->setCommand($objCommand)
			->setObjModel($objModel)
			->setAttributes($attributes)
			->setLabel($label)
			->setTitle($title)
			->setHref($strHref)
			->setChildRecordIds($arrChildRecordIds)
			->setCircularReference($blnCircularReference)
			->setPrevious($strPrevious)
			->setNext($strNext);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$buttonEvent,
			array(
				$this->getEnvironment()->getDataDefinition()->getName(),
				$objCommand->getName()
			)
		);

		// If the event created a button, use it.
		if (!is_null($buttonEvent->getHtml()))
		{
			return trim($buttonEvent->getHtml());
		}

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$buttonEvent->getHref(),
			specialchars($buttonEvent->getTitle()),
			$buttonEvent->getAttributes(),
			BackendBindings::generateImage($objCommand->getExtra()['icon'], $buttonEvent->getLabel())
		);
	}

	public function renderPasteIntoButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteInto()))
		{
			return $event->getHtmlPasteInto();
		}

		$strLabel = $this->translate('pasteinto/0', $event->getModel()->getProviderName());
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

	public function renderPasteAfterButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteAfter()))
		{
			return $event->getHtmlPasteAfter();
		}

		$strLabel = $this->translate('pasteafter/0', $event->getModel()->getProviderName());
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
		$commands = $this->getViewSection()->getModelCommands();

		$arrButtons = array();
		foreach ($commands->getCommands() as $command)
		{
			$arrButtons[$command->getName()] = $this->buildCommand($command, $objModelRow, $blnCircularReference, $arrChildRecordIds, $strPrevious, $strNext);
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

			$buttonEvent = new GetPasteButtonEvent($this->getEnvironment());
			$buttonEvent
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
			if ($this->getDataDefinition()->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL)
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
		if ($this->getPanel() === null)
		{
			throw new DcGeneralRuntimeException('No panel information stored in data container.');
		}

		$arrPanels = array();
		foreach ($this->getPanel() as $objPanel)
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
			$this
				->addToTemplate('action', ampersand($this->getEnvironment()->getInputProvider()->getRequestUrl(), true), $objTemplate)
				// ->addToTemplate('theme', $this->getTheme(), $objTemplate) // FIXME: dependency injection
				->addToTemplate('panel', $arrPanels, $objTemplate);

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
		$event = new GetBreadcrumbEvent($this->getEnvironment());

		$this->dispatchEvent(GetBreadcrumbEvent::NAME, $event);

		$arrReturn = $event->getElements();

		// Check if we have a result with elements
		if (!is_array($arrReturn) || count($arrReturn) == 0)
		{
			return null;
		}

		// Include the breadcrumb css
		$GLOBALS['TL_CSS'][] = 'system/modules/generalDriver/html/css/generalBreadcrumb.css';

		// Build template
		$objTemplate = $this->getTemplate('dcbe_general_breadcrumb');
		$this->addToTemplate('elements', $arrReturn, $objTemplate);

		return $objTemplate->parse();
	}

	/**
	 * Process input and return all modified properties or null if there is no input.
	 *
	 * @param ContaoWidgetManager $widgetManager
	 *
	 * @return null|PropertyValueBag
	 */
	public function processInput($widgetManager)
	{
		$input = $this->getEnvironment()->getInputProvider();

		if ($_POST && $input->getValue('FORM_SUBMIT') == $this->getEnvironment()->getDataDefinition()->getName())
		{
			$propertyValues = new PropertyValueBag();
			$propertyNames = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getPropertyNames();

			// process input and update changed properties.
			foreach ($propertyNames as $propertyName)
			{
				if ($input->hasValue($propertyName))
				{
					$propertyValue = $input->getValue($propertyName);
					$propertyValues->setPropertyValue($propertyName, $propertyValue);
				}
			}
			$widgetManager->processInput($propertyValues);

			return $propertyValues;
		}

		return null;
	}

	/**
	 * Get for a field the readable value
	 *
	 * @param PropertyInterface $property
	 * @param ModelInterface $model
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function getReadableFieldValue(PropertyInterface $property, ModelInterface $model, $value)
	{
		$event = new RenderReadablePropertyValueEvent($this->getEnvironment(), $model, $property, $value);
		$this->getEnvironment()->getEventPropagator()->propagate(
			$event,
			$this->getEnvironment()->getDataDefinition()->getName(),
			$property->getName()
		);

		if ($event->getRendered() !== null) {
			return $event->getRendered();
		}

		return $value;
	}
}
