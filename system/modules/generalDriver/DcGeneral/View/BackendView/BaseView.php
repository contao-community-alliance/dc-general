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

namespace DcGeneral\View\BackendView;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\Panel\LimitElementInterface;
use DcGeneral\Panel\PanelContainerInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\SubmitElementInterface;
use DcGeneral\View\ContaoBackendViewTemplate;
use DcGeneral\View\Widget\ContaoWidgetManager;
use DcGeneral\View\BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\View\BackendView\Event\GetEditModeButtonsEvent;
use DcGeneral\View\BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\View\BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\View\BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\View\BackendView\Event\GetOperationButtonEvent;
use DcGeneral\View\BackendView\Event\GetPasteButtonEvent;
use DcGeneral\View\BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\View\BackendView\Event\GetSelectModeButtonsEvent;

// TODO: this is not as elegant as it could be.
use DcGeneral\Contao\BackendBindings;

use DcGeneral\DataContainerInterface;
use DcGeneral\View\Widget\WidgetManagerInterface;

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
	 * The widget manager.
	 *
	 * @var WidgetManagerInterface
	 */
	protected $widgetManager;

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

		// Second, try to dispatch to all globally registered subscribers.
		if (!$event->isPropagationStopped())
		{
			$dispatcher->dispatch($eventName, $event);
		}
	}

	public function setEnvironment(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
		// TODO is this a good place to create the WidgetManager?
		$this->widgetManager = new ContaoWidgetManager($environment);
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
		if ($section !== null)
		{
			$this->getEnvironment()->getTranslationManager()->loadSection($section);
		}

		return $this->getEnvironment()->getTranslationManager()->getString($path);
	}

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
			$remoteNew = ($value != '') ? ucfirst($this->translate('MSC/yes')) : ucfirst($this->translate('MSC/no'));
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

			if ($month = $this->translate('MONTHS/' . $intMonth))
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
		static $lookup = array();

		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();
		$property    = $definition->getProperty($field);
		$options     = $property->get('options');
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
		if ($label = $this->translate($definition->getName() . '/' . $strButton))
		{
			return $label;
		}
		else if ($label = $this->translate('MSC/' . $strButton))
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
	 * FIXME: this code is only for legacy purposes for providing the functionality invented by s.heimes.
	 */
	protected function getLegacyEditButtons()
	{
		$buttons    = array();

		foreach ($this->getDC()->getButtonsDefinition() as $strButton => $arrButton)
		{
			// Check if the button has the label value itself
			if(!empty($arrButton['value']))
			{
				$strLabel = $arrButton['value'];
			}
			// else try to find a language array
			else
			{
				$strLabel = $this->getButtonLabel($strButton);
			}

			$buttons[$strButton] = sprintf(
				'<input type="submit" name="%s" id="%s" class="tl_submit%s" accesskey="%s" value="%s" />',
				$arrButton['formkey'],
				$arrButton['id'],
				(!empty($arrButton['class'])? ' ' . $arrButton['class'] : ''),
				$arrButton['accesskey'],
				$strLabel
			);
		}

		return $buttons;
	}

	/**
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array()
	 */
	protected function getEditButtons()
	{
		// TODO: remove call to getLegacyEditButtons() after grace period and solely use the values from events.
		// $buttons    = array();
		$buttons    = $this->getLegacyEditButtons();
		$definition = $this->getEnvironment()->getDataDefinition();
		$definition->isClosed();

		// TODO: we have hardcoded html in here, is this really the best idea?

		$buttons['save'] = sprintf(
			'<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="%s" />',
			$this->getButtonLabel('save')
		);

		$buttons['saveNclose'] = sprintf(
			'<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="%s" />',
			$this->getButtonLabel('saveNclose')
		);

		if (!($this->isPopup() || $definition->isClosed()) && $definition->isCreatable())
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
			&& (($definition->getSortingMode() == 4)
				|| strlen($definition->getParentDriverName())
				|| $definition->isSwitchToEdit()
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

		$this->dispatchEvent(GetEditModeButtonsEvent::NAME, $event);

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

		if ($definition->isDeletable())
		{
			$buttons['delete'] = sprintf(
				'<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'%s\')" value="%s">',
				$GLOBALS['TL_LANG']['MSC']['delAllConfirm'],
				specialchars($this->translate('MSC/deleteSelected'))
			);
		}

		// TODO: strictly spoken, cut is editing - should we wrap this within if ($definition->isEditable()) here?
		$buttons['cut'] = sprintf(
			'<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="%s">',
			specialchars($this->translate('MSC/moveSelected'))
		);

		$buttons['copy'] = sprintf(
			'<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="%s">',
			specialchars($this->translate('MSC/copySelected'))
		);

		if ($definition->isEditable())
		{
			$buttons['override'] = sprintf(
				'<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="%s">',
				specialchars($this->translate('MSC/overrideSelected'))
			);

			$buttons['edit'] = sprintf(
				'<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="%s">',
				specialchars($this->translate('MSC/editSelected'))
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
				if ($this->getEnvironment()->getDataDefinition()->getSortingMode() == 5)
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
		$objDataProvider = $environment->getDataDriver();
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

	/**
	 * Generate the view for edit
	 *
	 * @return string
	 */
	public function edit()
	{
		// Load basic informations
		$this->checkLanguage();

		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();
		$driver      = $environment->getDataDriver();
		$model       = $environment->getCurrentModel();

		// Get all selectors
		$this->arrStack[] = $definition->getSubPalettes();
		$this->calculateSelectors($this->arrStack[0]);
		$this->parseRootPalette();

		if ($model->getId())
		{
			$strHeadline = sprintf($this->translate('MSC/editRecord'), 'ID ' . $model->getId());
		}
		else
		{
			// TODO: new language string for "new" model?
			$strHeadline = sprintf($this->translate('MSC/editRecord'), '');
		}

		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_edit');
		$objTemplate->setData(array(
			'fieldsets' => $this->generateFieldsets('dcbe_general_field', array()),
			'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'versions' => $driver->getVersions($model->getId()),
			'subHeadline' => $strHeadline,
			'table' => $definition->getName(),
			'enctype' => $this->getDC()->isUploadable() ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
			//'onsubmit' => implode(' ', $this->onsubmit),
			'error' => $this->noReload,
			'editButtons' => $this->getEditButtons(),
			'noReload' => $this->getDC()->isNoReload()
		));

		if ($this->isMultiLanguage($model->getId()))
		{
			$langsNative = array();
			include(TL_ROOT . '/system/config/languages.php');

			/** @var MultiLanguageDriverInterface $driver */
			$this
				->addToTemplate('languages', $environment->getController()->getSupportedLanguages($model->getId()), $objTemplate)
				->addToTemplate('language', $driver->getCurrentLanguage(), $objTemplate)
				->addToTemplate('languageHeadline', $langsNative[$driver->getCurrentLanguage()], $objTemplate);
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
		$environment     = $this->getEnvironment();
		$definition      = $environment->getDataDefinition();
		$objDataProvider = $environment->getDataDriver();
		$mixId           = $environment->getInputProvider()->getParameter('id');

		// Check
		$this->checkLanguage();

		// Load record from data provider
		$objDBModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($mixId));

		if ($objDBModel == null)
		{
			$this->log('Could not find ID ' . $mixId . ' in Table ' . $definition->getName() . '.', 'DC_General show()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
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
		if ($definition->getPropertyNames())
		{
			$allowedFields = array_unique(array_merge($allowedFields, $definition->getPropertyNames()));
		}

		$fields = array_intersect($allowedFields, $fields);

		// Show all allowed fields
		foreach ($fields as $strFieldName)
		{
			$objProperty = $definition->getProperty($strFieldName);
			// TODO: we should examine the palette here and hide irrelevant fields.
			if ($objProperty)
			{
				$eval = $objProperty->getEvaluation();

				if (!in_array($strFieldName, $allowedFields)
					|| $objProperty->getWidgetType() == 'password'
					|| ($eval && (
							array_key_exists('doNotShow', $eval)
							|| array_key_exists('hideInput', $eval))))
				{
					continue;
				}
				$label = $objProperty->getLabel();
			}
			else
			{
				$label = '';
			}

			// Make it human readable
			$arrFieldValues[$strFieldName] = $this->getDC()->getReadableFieldValue($strFieldName, deserialize($this->getCurrentModel()->getProperty($strFieldName)));

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
			->addToTemplate('headline', sprintf($this->translate('MSC/showRecord'), ($objDBModel->getId() ? 'ID ' . $objDBModel->getId() : '')), $objTemplate)
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
		$arrFieldsetStates = $this->getEnvironment()->getInputProvider()->getPersistentValue('fieldset_states');
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

		$strName = null;
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
					$strDatepicker = $this->buildDatePicker($objWidget);
				}

				// TODO: Maybe TemplateFoo is not such a good name :?
				// FIXME: dependency injection or rather template factory?

				$objTemplateFoo = $this->getTemplate($strFieldTemplate);

				$this
					->addToTemplate('strName', $strName, $objTemplateFoo)
					->addToTemplate('strClass', $strClass, $objTemplateFoo)
					->addToTemplate('objWidget', $objWidget, $objTemplateFoo)
					->addToTemplate('strDatepicker', $strDatepicker, $objTemplateFoo)
					->addToTemplate('blnUpdate', $blnUpdate, $objTemplateFoo)
					->addToTemplate('strHelp', $this->getDC()->generateHelpText($varField), $objTemplateFoo);

				echo $objTemplateFoo->parse();

				if (strncmp($arrConfig['eval']['rte'], 'tiny', 4) === 0)
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
			'startDay' => $this->translate('MSC/weekOffset'),
			'days' => array_values((array)$this->translate('MSC/DAYS')),
			'dayShort' => $this->translate('MSC/dayShortLength'),
			'months' => array_values((array)$this->translate('MSC/MONTHS')),
			'monthShort' => $this->translate('MSC/monthShortLength')
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
			format:"' . \Date::formatToJs($strFormat) . '",
			positionOffset:{x:-197,y:-182}' . $time . ',
			pickerClass:"datepicker_dashboard",
			useFadeInOut:!Browser.ie,
			startDay:' . $this->translate('MSC/weekOffset') . ',
			titleFormat:"' . $this->translate('MSC/titleFormat') . '"
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
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$providerName     = $environment->getDataDefinition()->getName();
		$arrReturn        = array();
		$globalOperations = $this->getDC()->arrDCA['list']['global_operations'];


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

			// Add Buttons for mode x
			switch ($definition->getSortingMode())
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
						if ($definition->getSortingMode() < 4)
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
							'title'      => $this->translate($providerName . '/new/1'),
							'label'      => $this->translate($providerName . '/new/0')
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
						'title'      => $this->translate('MSC/clearClipboard'),
						'label'      => $this->translate('MSC/clearClipboard')
					)
				)
				, $globalOperations
			);
		}

		// Add back button
		if ($this->isSelectModeActive() || $this->getEnvironment()->getDataDefinition()->getParentDriverName())
		{
			$globalOperations = array_merge(
				array(
					'back_button'    => array
					(
						'class'      => 'header_back',
						'accesskey'  => 'b',
						'href'       => BackendBindings::getReferer(true, $this->getEnvironment()->getDataDefinition()->getParentDriverName()),
						'attributes' => 'onclick="Backend.getScrollOffset();"',
						'title'      => $this->translate('MSC/backBT'),
						'label'      => $this->translate('MSC/backBT')
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

		$buttonEvent = new GetOperationButtonEvent($this->getEnvironment());
		$buttonEvent
			->setObjOperation($objOperation)
			->setObjModel($objModel)
			->setAttributes($attributes)
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

	public function renderPasteIntoButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteInto()))
		{
			return $event->getHtmlPasteInto();
		}

		$strLabel = $this->translate($event->getModel()->getProviderName() . '/pasteinto/0');
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

		$strLabel = $this->translate($event->getModel()->getProviderName() . '/pasteafter/0');
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
	 * {@inheritdoc}
	 */
	public function processInput()
	{
		$input = $this->getEnvironment()->getInputProvider();

		if ($_POST && $input->getValue('FORM_SUBMIT') == $this->getEnvironment()->getDataDefinition()->getName())
		{
			$propertyValues = new PropertyValueBag();
			$propertyNames = $this->getEnvironment()->getDataDefinition()->getPropertyNames();

			// process input and update changed properties.
			foreach ($propertyNames as $propertyName)
			{
				if ($input->hasValue($propertyName))
				{
					$propertyValue = $input->getValue($propertyName);
					$propertyValues->setPropertyValue($propertyName, $propertyValue);
				}
			}

			$this->widgetManager->processInput($propertyValues);

			return $propertyValues;
		}

		return null;
	}
}
