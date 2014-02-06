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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EditModelBeforeSaveEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use DcGeneral\DataDefinition\Definition\View\CommandInterface;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\PostCreateModelEvent;
use DcGeneral\Event\PostDeleteModelEvent;
use DcGeneral\Event\PostPersistModelEvent;
use DcGeneral\Event\PreCreateModelEvent;
use DcGeneral\Event\PreDeleteModelEvent;
use DcGeneral\Event\PrePersistModelEvent;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\Panel\LimitElementInterface;
use DcGeneral\Panel\PanelContainerInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\SubmitElementInterface;
use DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use DcGeneral\Contao\BackendBindings;

/**
 * Class BaseView.
 *
 * This class is the base class for the different backend view mode sub classes.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class BaseView implements BackendViewInterface
{
	/**
	 * The error message format string to use when a method is not implemented.
	 *
	 * @var string
	 */
	protected $notImplMsg =
		'<div style="text-align:center; font-weight:bold; padding:40px;">
		The function/view &quot;%s&quot; is not implemented.
		</div>';

	/**
	 * The attached environment.
	 *
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * The panel container in use.
	 *
	 * @var PanelContainerInterface
	 */
	protected $panel;

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
	 * @param string                                   $eventName The name of the event to dispatch.
	 *
	 * @param \Symfony\Component\EventDispatcher\Event $event     The event to dispatch.
	 *
	 * @return void
	 *
	 * @deprecated Use $this->getEnvironment()->getEventPropagator()->propagate() instead.
	 */
	protected function dispatchEvent($eventName, $event)
	{
		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEnvironment(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Retrieve the data definition from the environment.
	 *
	 * @return \DcGeneral\DataDefinition\ContainerInterface
	 */
	protected function getDataDefinition()
	{
		return $this->getEnvironment()->getDataDefinition();
	}

	/**
	 * Translate a string via the translator.
	 *
	 * @param string      $path    The path within the translation where the string can be found.
	 *
	 * @param string|null $section The section from which the translation shall be retrieved.
	 *
	 * @return string
	 */
	protected function translate($path, $section = null)
	{
		return $this->getEnvironment()->getTranslator()->translate($path, $section);
	}

	/**
	 * Add the value to the template.
	 *
	 * @param string    $name     Name of the value.
	 *
	 * @param mixed     $value    The value to add to the template.
	 *
	 * @param \Template $template The template to add the value to.
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
	 * Redirects to the real back end module.
	 *
	 * @return void
	 */
	protected function redirectHome()
	{
		$environment = $this->getEnvironment();
		$input       = $environment->getInputProvider();

		if ($input->hasParameter('table') && $input->hasParameter('pid'))
		{
			if ($input->hasParameter('pid'))
			{
				$event = new RedirectEvent(sprintf(
						'contao/main.php?do=%s&table=%s&pid=%s',
						$input->getParameter('do'),
						$input->getParameter('table'),
						$input->getParameter('pid')
				));
			}
			else
			{
				$event = new RedirectEvent(sprintf(
					'contao/main.php?do=%s&table=%s',
					$input->getParameter('do'),
					$input->getParameter('table')
				));
			}
		}
		else
		{
			$event = new RedirectEvent(sprintf(
				'contao/main.php?do=%s',
				$input->getParameter('do')
			));
		}

		$environment->getEventPropagator()->propagate(ContaoEvents::CONTROLLER_REDIRECT, $event);
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

	/**
	 * Determine if the select mode is currently active or not.
	 *
	 * @return bool
	 */
	protected function isSelectModeActive()
	{
		return \Input::getInstance()->get('act') == 'select';
	}

	/**
	 * Retrieve the currently active grouping mode.
	 *
	 * @return array|null
	 *
	 * @see    ListingConfigInterface
	 */
	protected function getGroupingMode()
	{
		/** @var Contao2BackendViewDefinitionInterface $viewDefinition */
		$viewDefinition = $this->getViewSection();
		$listingConfig  = $viewDefinition->getListingConfig();

		if ($listingConfig->getSortingMode() === ListingConfigInterface::SORT_RANDOM)
		{
			return null;
		}

		$definition    = $this->getEnvironment()->getDataDefinition();
		$properties    = $definition->getPropertiesDefinition();
		$sortingFields = array_keys((array)$listingConfig->getDefaultSortingFields());
		$firstSorting  = reset($sortingFields);

		$panel = $this->getPanel()->getPanel('sorting');
		if ($panel)
		{
			/** @var \DcGeneral\Panel\SortElementInterface $panel */
			$firstSorting = $panel->getSelected();
		}

		// Get the current value of first sorting.
		if (!$firstSorting)
		{
			return null;
		}

		$property = $properties->getProperty($firstSorting);

		if (count($sortingFields) == 0)
		{
			$groupMode   = ListingConfigInterface::GROUP_NONE;
			$groupLength = 0;
		}
		// Use the information from the property, if given.
		elseif ($property->getGroupingMode() != '')
		{
			$groupMode   = $property->getGroupingMode();
			$groupLength = $property->getGroupingLength();
		}
		// Use the global as fallback.
		else
		{
			$groupMode   = $listingConfig->getGroupingMode();
			$groupLength = $listingConfig->getGroupingLength();
		}

		return array
		(
			'mode'     => $groupMode,
			'length'   => $groupLength,
			'property' => $firstSorting
		);
	}

	/**
	 * Return the formatted value for use in group headers as string.
	 *
	 * @param string         $field       The name of the property to format.
	 *
	 * @param ModelInterface $model       The model from which the value shall be taken from.
	 *
	 * @param string         $groupMode   The grouping mode in use.
	 *
	 * @param int            $groupLength The length of the value to use for grouping (only used when grouping mode is
	 *                                    ListingConfigInterface::GROUP_CHAR).
	 *
	 * @return string
	 */
	public function formatCurrentValue($field, $model, $groupMode, $groupLength)
	{
		$value    = $model->getProperty($field);
		$property = $this->getDataDefinition()->getPropertiesDefinition()->getProperty($field);

		// No property? Get out!
		if (!$property)
		{
			return '-';
		}

		$evaluation = $property->getExtra();
		$remoteNew  = '';

		if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple'])
		{
			$remoteNew = ($value != '') ? ucfirst($this->translate('MSC.yes')) : ucfirst($this->translate('MSC.no'));
		}
		// TODO: refactor foreignKey is yet undefined.
		elseif (false && $property->getForeignKey())
		{
			// TODO: case handling.
			if ($objParentModel->hasProperties())
			{
				$remoteNew = $objParentModel->getProperty('value');
			}

		}
		elseif ($groupMode != ListingConfigInterface::GROUP_NONE)
		{
			switch ($groupMode)
			{
				case ListingConfigInterface::GROUP_CHAR:
					$remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, $groupLength)) : '-';
					break;

				case ListingConfigInterface::GROUP_DAY:
					$remoteNew = ($value != '') ? BackendBindings::parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '-';
					break;

				case ListingConfigInterface::GROUP_MONTH:
					$remoteNew = ($value != '') ? date('Y-m', $value) : '-';
					$intMonth  = ($value != '') ? (date('m', $value) - 1) : '-';

					if ($month = $this->translate('MONTHS' . $intMonth))
					{
						$remoteNew = ($value != '') ? $month . ' ' . date('Y', $value) : '-';
					}
					break;

				case ListingConfigInterface::GROUP_YEAR:
					$remoteNew = ($value != '') ? date('Y', $value) : '-';
					break;

				default:
			}
		}
		else
		{
			if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple'])
			{
				$remoteNew = ($value != '') ? $field : '';
			}
			// TODO: refactor reference is yet undefined.
			elseif (false && is_array($property->get('reference')))
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

		$event = new GetGroupHeaderEvent($this->getEnvironment(), $model, $field, $remoteNew, $groupMode);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);

		$remoteNew = $event->getValue();

		return $remoteNew;
	}

	/**
	 * Get the label for a button from the translator.
	 *
	 * The fallback is as follows:
	 * 1. Try to translate the button via the data definition name as translation section.
	 * 2. Try to translate the button name with the prefix 'MSC.'.
	 * 3. Return the input value as nothing worked out.
	 *
	 * @param string $strButton The non translated label for the button.
	 *
	 * @return string
	 */
	protected function getButtonLabel($strButton)
	{
		$definition = $this->getEnvironment()->getDataDefinition();
		if (($label = $this->translate($strButton, $definition->getName())) !== $strButton)
		{
			return $label;
		}
		elseif (($label = $this->translate('MSC.' . $strButton)) !== $strButton)
		{
			return $label;
		}

		// Fallback, just return the key as is it.
		return $strButton;
	}

	/**
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array
	 */
	protected function getEditButtons()
	{
		$buttons         = array();
		$definition      = $this->getEnvironment()->getDataDefinition();
		$basicDefinition = $definition->getBasicDefinition();

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
		elseif(!$this->isPopup()
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

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($definition->getName())
		);

		return $event->getButtons();
	}

	/**
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array
	 */
	protected function getSelectButtons()
	{
		$definition      = $this->getDataDefinition();
		$basicDefinition = $definition->getBasicDefinition();
		$buttons         = array();

		if ($basicDefinition->isDeletable())
		{
			$buttons['delete'] = sprintf(
				'<input
				type="submit"
				name="delete"
				id="delete"
				class="tl_submit"
				accesskey="d"
				onclick="return confirm(\'%s\')"
				value="%s" />',
				$GLOBALS['TL_LANG']['MSC']['delAllConfirm'],
				specialchars($this->translate('MSC.deleteSelected'))
			);
		}

		if ($basicDefinition->isEditable())
		{
			$buttons['cut'] = sprintf(
				'<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="%s">',
				specialchars($this->translate('MSC.moveSelected'))
			);
		}

		if ($basicDefinition->isCreatable())
		{
			$buttons['copy'] = sprintf(
				'<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="%s">',
				specialchars($this->translate('MSC.copySelected'))
			);
		}

		if ($basicDefinition->isEditable())
		{
			$buttons['override'] = sprintf(
				'<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="%s">',
				specialchars($this->translate('MSC.overrideSelected'))
			);

			$buttons['edit'] = sprintf(
				'<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="%s">',
				specialchars($this->translate('MSC.editSelected'))
			);
		}

		$event = new GetSelectModeButtonsEvent($this->getEnvironment());
		$event->setButtons($buttons);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);

		return $event->getButtons();
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
	 *
	 * @return BaseView
	 */
	public function checkClipboard()
	{
		$objInput     = $this->getEnvironment()->getInputProvider();
		$objClipboard = $this->getEnvironment()->getClipboard();

		// Reset Clipboard.
		if ($objInput->getParameter('clipboard') == '1')
		{
			$objClipboard->clear();
		}
		// Push some entry into clipboard.
		elseif ($objInput->getParameter('act') == 'paste')
		{
			$objDataProv = $this->getEnvironment()->getDataProvider();
			$id          = $objInput->getParameter('id');

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

	/**
	 * Determine if we are currently working in multi language mode.
	 *
	 * @param mixed $mixId The id of the current model.
	 *
	 * @return bool
	 */
	protected function isMultiLanguage($mixId)
	{
		return count($this->getEnvironment()->getController()->getSupportedLanguages($mixId)) > 0;
	}

	/**
	 * Check if the data provider is multi language and prepare the data provider with the selected language.
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

		// Load language from Session.
		$arrSession = $inputProvider->getPersistentValue('dc_general');
		if (!is_array($arrSession))
		{
			$arrSession = array();
		}
		/** @var \DcGeneral\Data\MultiLanguageDriverInterface $objDataProvider */

		// Try to get the language from session.
		if (isset($arrSession['ml_support'][$strProviderName][$mixID]))
		{
			$strCurrentLanguage = $arrSession['ml_support'][$strProviderName][$mixID];
		}
		else
		{
			$strCurrentLanguage = $GLOBALS['TL_LANGUAGE'];
		}

		// Get/Check the new language.
		if ((strlen($inputProvider->getValue('language')) != 0)
			&& ($inputProvider->getValue('FORM_SUBMIT') == 'language_switch'))
		{
			if (array_key_exists($inputProvider->getValue('language'), $arrLanguage))
			{
				$strCurrentLanguage = $inputProvider->getValue('language');
			}
		}

		if (!array_key_exists($strCurrentLanguage, $arrLanguage))
		{
			$strCurrentLanguage = $objDataProvider->getFallbackLanguage($mixID)->getID();
		}

		$arrSession['ml_support'][$strProviderName][$mixID] = $strCurrentLanguage;
		$inputProvider->setPersistentValue('dc_general', $arrSession);

		$objDataProvider->setCurrentLanguage($strCurrentLanguage);
	}

	/**
	 * Create a new instance of ContaoBackendViewTemplate with the template file of the given name.
	 *
	 * @param string $strTemplate Name of the template to create.
	 *
	 * @return ContaoBackendViewTemplate
	 */
	protected function getTemplate($strTemplate)
	{
		return new ContaoBackendViewTemplate($strTemplate);
	}

	/**
	 * TODO: Handle an ajax call, this method is currently not implemented.
	 *
	 * @return string
	 */
	public function handleAjaxCall()
	{
		$action = $this->getEnvironment()->getInputProvider()->getValue('action');
		return vsprintf($this->notImplMsg, 'handleAjaxCall()');
	}

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
	 * Create a new item.
	 *
	 * @see    edit()
	 *
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
	 * Delete a model and redirect the user to the listing.
	 *
	 * NOTE: This method redirects the user to the listing and therefore the script will be ended.
	 *
	 * @return void
	 */
	public function delete()
	{
		$environment  = $this->getEnvironment();
		$dataProvider = $environment->getDataProvider();
		$modelId      = $environment->getInputProvider()->getParameter('id');
		$model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId));

		// Trigger event befor the model will be deleted.
		$event = new PreDeleteModelEvent($environment, $model);
		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$this->getEnvironment()->getDataDefinition()->getName(),
			)
		);

		$dataProvider->delete($model);

		// Trigger event after the model is deleted.
		$event = new PostDeleteModelEvent($environment, $model);
		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$this->getEnvironment()->getDataDefinition()->getName(),
			)
		);

		$this->redirectHome();
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
	 * Handle the submit and determine which button has been triggered.
	 *
	 * This method will redirect the client.
	 *
	 * @param ModelInterface $model The model that has been submitted.
	 *
	 * @return void
	 */
	protected function handleSubmit(ModelInterface $model)
	{
		$environment   = $this->getEnvironment();
		$inputProvider = $environment->getInputProvider();

		if ($inputProvider->hasValue('save'))
		{
			if ($inputProvider->getParameter('id'))
			{
				$environment->getEventPropagator()->propagate(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
			}
			else
			{
				$newUrlEvent = new AddToUrlEvent('id=' . $model->getId());
				$environment->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $newUrlEvent);
				$environment->getEventPropagator()->propagate(
					ContaoEvents::CONTROLLER_REDIRECT,
					new RedirectEvent($newUrlEvent->getUrl())
				);
			}
		}
		elseif ($inputProvider->hasValue('saveNclose'))
		{
			setcookie('BE_PAGE_OFFSET', 0, 0, '/');

			$_SESSION['TL_INFO']    = '';
			$_SESSION['TL_ERROR']   = '';
			$_SESSION['TL_CONFIRM'] = '';

			$newUrlEvent = new GetReferrerEvent();
			$environment->getEventPropagator()->propagate(ContaoEvents::SYSTEM_GET_REFERRER, $newUrlEvent);
			$environment->getEventPropagator()->propagate(
				ContaoEvents::CONTROLLER_REDIRECT,
				new RedirectEvent($newUrlEvent->getReferrerUrl())
			);
		}
		elseif ($inputProvider->hasValue('saveNcreate'))
		{
			setcookie('BE_PAGE_OFFSET', 0, 0, '/');

			$_SESSION['TL_INFO']    = '';
			$_SESSION['TL_ERROR']   = '';
			$_SESSION['TL_CONFIRM'] = '';

			$newUrlEvent = new AddToUrlEvent('act=create&id=');
			$environment->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $newUrlEvent);
			$environment->getEventPropagator()->propagate(
				ContaoEvents::CONTROLLER_REDIRECT,
				new RedirectEvent($newUrlEvent->getUrl())
			);

		}
		elseif ($inputProvider->hasValue('saveNback'))
		{
			echo vsprintf($this->notImplMsg, 'Save and go back');
			exit;
		}
		else
		{
			// Custom button logic.

		}
	}

	/**
	 * Check the submitted data if we want to restore a previous version of a model.
	 *
	 * If so, the model will get loaded and marked as active version in the data provider and the client will perform a
	 * reload of the page.
	 *
	 * @return void
	 *
	 * @throws DcGeneralRuntimeException When the requested version could not be located in the database.
	 */
	protected function checkRestoreVersion()
	{
		$environment             = $this->getEnvironment();
		$definition              = $environment->getDataDefinition();
		$basicDefinition         = $definition->getBasicDefinition();
		$dataProviderDefinition  = $definition->getDataProviderDefinition();
		$dataProvider            = $environment->getDataProvider();
		$dataProviderInformation = $dataProviderDefinition->getInformation($basicDefinition->getDataProvider());
		$inputProvider           = $environment->getInputProvider();
		$modelId                 = $inputProvider->getParameter('id');

		if ($dataProviderInformation->isVersioningEnabled()
			&& ($inputProvider->getValue('FORM_SUBMIT') === 'tl_version')
			&& ($modelVersion = $inputProvider->getValue('version')) !== null)
		{
			$model = $dataProvider->getVersion($modelId, $modelVersion);

			if ($model === null)
			{
				$message = sprintf(
					'Could not load version %s of record ID %s from %s',
					$modelVersion,
					$modelId,
					$basicDefinition->getDataProvider()
				);

				$environment->getEventPropagator()->propagate(
					ContaoEvents::SYSTEM_LOG,
					new LogEvent($message, TL_ERROR, 'DC_General - checkRestoreVersion()')
				);

				throw new DcGeneralRuntimeException($message);
			}

			$dataProvider->save($model);
			$dataProvider->setVersionActive($modelId, $modelVersion);
			$environment->getEventPropagator()->propagate(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
		}
	}

	/**
	 * Abstract method to be overridden in the certain child classes.
	 *
	 * This method will update the parent relationship between a model and the parent item.
	 *
	 * @param ModelInterface $model The model to be updated.
	 *
	 * @return void
	 */
	public function enforceModelRelationship($model)
	{
		// No op in this base class but implemented in subclasses to enforce parent<->child relationship.
	}

	/**
	 * Create an empty model using the default values from the definition.
	 *
	 * @return ModelInterface
	 */
	protected function createEmptyModelWithDefaults()
	{
		$environment        = $this->getEnvironment();
		$definition         = $environment->getDataDefinition();
		$environment        = $this->getEnvironment();
		$dataProvider       = $environment->getDataProvider();
		$propertyDefinition = $definition->getPropertiesDefinition();
		$properties         = $propertyDefinition->getProperties();
		$model              = $dataProvider->getEmptyModel();

		foreach ($properties as $property)
		{
			$propName = $property->getName();
			$model->setProperty($propName, $property->getDefaultValue());
		}

		return $model;
	}

	/**
	 * Update the versioning information in the data provider for a given model (if necessary).
	 *
	 * @param ModelInterface $model The model to update.
	 *
	 * @return void
	 */
	protected function storeVersion(ModelInterface $model)
	{
		$modelId                 = $model->getId();
		$environment             = $this->getEnvironment();
		$definition              = $environment->getDataDefinition();
		$basicDefinition         = $definition->getBasicDefinition();
		$dataProvider            = $environment->getDataProvider();
		$dataProviderDefinition  = $definition->getDataProviderDefinition();
		$dataProviderInformation = $dataProviderDefinition->getInformation($basicDefinition->getDataProvider());

		if (!$dataProviderInformation->isVersioningEnabled())
		{
			return;
		}

		// Compare version and current record.
		$currentVersion = $dataProvider->getActiveVersion($modelId);
		if (!$currentVersion
			|| !$dataProvider->sameModels($model, $dataProvider->getVersion($modelId, $currentVersion))
		)
		{
			$user = \BackendUser::getInstance();

			$dataProvider->saveVersion($model, $user->username);
		}
	}

	/**
	 * Generate the view for edit.
	 *
	 * @return string
	 *
	 * @throws DcGeneralRuntimeException         When the current data definition is not editable or is closed.
	 *
	 * @throws DcGeneralInvalidArgumentException When an unknown property is mentioned in the palette.
	 */
	public function edit()
	{
		$this->checkLanguage();

		$environment             = $this->getEnvironment();
		$definition              = $environment->getDataDefinition();
		$basicDefinition         = $definition->getBasicDefinition();
		$dataProvider            = $environment->getDataProvider();
		$dataProviderDefinition  = $definition->getDataProviderDefinition();
		$dataProviderInformation = $dataProviderDefinition->getInformation($basicDefinition->getDataProvider());
		$inputProvider           = $environment->getInputProvider();
		$palettesDefinition      = $definition->getPalettesDefinition();
		$modelId                 = $inputProvider->getParameter('id');
		$propertyDefinitions     = $definition->getPropertiesDefinition();
		$blnSubmitted            = ($inputProvider->getValue('FORM_SUBMIT') === $definition->getName());
		$blnIsAutoSubmit         = ($inputProvider->getValue('SUBMIT_TYPE') === 'auto');
		$blnNewEntry             = false;

		$this->checkRestoreVersion();

		if (strlen($modelId))
		{
			$model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId));
		}
		else
		{
			$model       = $this->createEmptyModelWithDefaults();
			$blnNewEntry = true;
		}

		// We need to keep the original data here.
		$originalModel = clone $model;
		$originalModel->setId($model->getId());

		$widgetManager = new ContaoWidgetManager($environment, $model);

		// Check if table is editable.
		if (!$basicDefinition->isEditable())
		{
			$message = 'DataContainer ' . $definition->getName() . ' is not editable';
			$environment->getEventPropagator()->propagate(
				ContaoEvents::SYSTEM_LOG,
				new LogEvent($message, TL_ERROR, 'DC_General - edit()')
			);
			throw new DcGeneralRuntimeException($message);
		}

		// Check if table is closed but we are adding a new item.
		if ((!$modelId) && $basicDefinition->isClosed())
		{
			$message = 'DataContainer ' . $definition->getName() . ' is closed';
			$environment->getEventPropagator()->propagate(
				ContaoEvents::SYSTEM_LOG,
				new LogEvent($message, TL_ERROR, 'DC_General - edit()')
			);
			throw new DcGeneralRuntimeException($message);
		}

		$this->enforceModelRelationship($model);

		// Pass 1: Get the palette for the values stored in the model.
		$palette = $palettesDefinition->findPalette($model);

		$propertyValues = $this->processInput($widgetManager);
		$errors         = array();
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
			$properties = $legend->getProperties($model, $propertyValues);

			if (!$properties)
			{
				continue;
			}

			foreach ($properties as $property)
			{
				if (!$propertyDefinitions->hasProperty($property->getName()))
				{
					throw new DcGeneralInvalidArgumentException(
						sprintf(
							'Property %s is mentioned in palette but not defined in propertyDefinition.',
							$property->getName()
						)
					);
				}

				// If this property is invalid, fetch the error.
				if ((!$blnIsAutoSubmit)
					&& $propertyValues
					&& $propertyValues->hasPropertyValue($property->getName())
					&& $propertyValues->isPropertyValueInvalid($property->getName())
				)
				{
					$errors = array_merge($errors, $propertyValues->getPropertyValueErrors($property->getName()));
				}

				$fields[] = $widgetManager->renderWidget($property->getName(), $blnIsAutoSubmit);
			}

			$arrFieldSet['label']   = $legendName;
			$arrFieldSet['class']   = 'tl_box';
			$arrFieldSet['palette'] = implode('', $fields);
			$arrFieldSet['legend']  = $legend->getName();
			$arrFieldSets[]         = $arrFieldSet;
		}

		if ((!$blnIsAutoSubmit) && $blnSubmitted && empty($errors))
		{
			if ($model->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				// Trigger the event for post persists or create.
				if ($blnNewEntry)
				{
					$createEvent = new PreCreateModelEvent($this->getEnvironment(), $model);
					$environment->getEventPropagator()->propagate(
						$createEvent::NAME,
						$createEvent,
						array(
							$this->getEnvironment()->getDataDefinition()->getName(),
						)
					);
				}

				$event = new PrePersistModelEvent($environment, $model, $originalModel);
				$environment->getEventPropagator()->propagate(
					$event::NAME,
					$event,
					array(
						$this->getEnvironment()->getDataDefinition()->getName(),
					)
				);

				//Save the model.
				$dataProvider->save($model);

				// Trigger the event for post persists or create.
				if ($blnNewEntry)
				{
					$event = new PostCreateModelEvent($environment, $model);
					$environment->getEventPropagator()->propagate(
						$event::NAME,
						$event,
						array(
							$this->getEnvironment()->getDataDefinition()->getName(),
						)
					);
				}

				$event = new PostPersistModelEvent($environment, $model, $originalModel);
				$environment->getEventPropagator()->propagate(
					$event::NAME,
					$event,
					array(
						$this->getEnvironment()->getDataDefinition()->getName(),
					)
				);

				$this->storeVersion($model);
			}

			$this->handleSubmit($model);
		}

		if ($model->getId())
		{
			$strHeadline = sprintf($this->translate('editRecord', $definition->getName()), 'ID ' . $model->getId());
			if ($strHeadline === 'editRecord')
			{
				$strHeadline = sprintf($this->translate('MSC.editRecord'), 'ID ' . $model->getId());
			}
		}
		else
		{
			$strHeadline = sprintf($this->translate('newRecord', $definition->getName()), 'ID ' . $model->getId());
			if ($strHeadline === 'newRecord')
			{
				$strHeadline = sprintf($this->translate('MSC.editRecord'), '');
			}
		}

		$objTemplate = $this->getTemplate('dcbe_general_edit');
		$objTemplate->setData(array(
			'fieldsets' => $arrFieldSets,
			'versions' => $dataProviderInformation->isVersioningEnabled() ? $dataProvider->getVersions($model->getId()) : null,
			'subHeadline' => $strHeadline,
			'table' => $definition->getName(),
			'enctype' => 'multipart/form-data',
			'error' => $errors,
			'editButtons' => $this->getEditButtons(),
			'noReload' => (bool)$errors
		));

		if ($this->isMultiLanguage($model->getId()))
		{
			/** @var MultiLanguageDriverInterface $dataProvider */
			$langsNative = array();
			require TL_ROOT . '/system/config/languages.php';

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
	 * Calculate the label of a property to se in "show" view.
	 *
	 * @param PropertyInterface $property The property for which the label shall be calculated.
	 *
	 * @return string
	 */
	protected function getLabelForShow(PropertyInterface $property)
	{
		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();

		$label = $environment->getTranslator()->translate($property->getLabel(), $definition->getName());

		if (!$label)
		{
			$label = $environment->getTranslator()->translate('MSC.' . $property->getName());
		}

		if (is_array($label))
		{
			$label = $label[0];
		}

		if (!$label)
		{
			$label = $property->getName();
		}

		return $label;
	}

	/**
	 * Show Information about a model.
	 *
	 * @return string
	 *
	 * @throws DcGeneralRuntimeException When an unknown property is mentioned in the palette.
	 */
	public function show()
	{
		// Load check multi language.
		$environment  = $this->getEnvironment();
		$definition   = $environment->getDataDefinition();
		$properties   = $definition->getPropertiesDefinition();
		$translator   = $environment->getTranslator();
		$dataProvider = $environment->getDataProvider();
		$modelId      = $environment->getInputProvider()->getParameter('id');

		// Select language in data provider.
		$this->checkLanguage();

		$objDBModel = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId));

		if ($objDBModel == null)
		{
			$environment->getEventPropagator()->propagate(
				ContaoEvents::SYSTEM_LOG,
				new LogEvent(
					sprintf(
						'Could not find ID %s in %s.', 'DC_General show()',
						$modelId,
						$definition->getName()
					),
					__CLASS__ . '::' . __FUNCTION__,
					TL_ERROR
				)
			);

			$environment->getEventPropagator()->propagate(
				ContaoEvents::CONTROLLER_REDIRECT,
				new RedirectEvent('contao/main.php?act=error')
			);
		}

		$values = array();
		$labels = array();

		$palette = $definition->getPalettesDefinition()->findPalette($objDBModel);

		// Show only allowed fields.
		foreach ($palette->getVisibleProperties($objDBModel) as $paletteProperty)
		{
			$property = $properties->getProperty($paletteProperty->getName());

			if (!$property)
			{
				throw new DcGeneralRuntimeException('Unable to retrieve property ' . $paletteProperty->getName());
			}

			// Make it human readable.
			$values[$paletteProperty->getName()] = $this->getReadableFieldValue(
				$property,
				$objDBModel,
				$objDBModel->getProperty($paletteProperty->getName())
			);
			$labels[$paletteProperty->getName()] = $this->getLabelForShow($property);
		}

		$headline = $translator->translate(
			'MSC.showRecord',
			$definition->getName(),
			array($objDBModel->getId() ? 'ID ' . $objDBModel->getId() : '')
		);

		if ($headline == 'MSC.showRecord')
		{
			$headline = $translator->translate(
				'MSC.showRecord',
				null,
				array($objDBModel->getId() ? 'ID ' . $objDBModel->getId() : '')
			);
		}

		$template = $this->getTemplate('dcbe_general_show');
		$this
			->addToTemplate('headline', $headline, $template)
			->addToTemplate('arrFields', $values, $template)
			->addToTemplate('arrLabels', $labels, $template);

		if ($this->isMultiLanguage($objDBModel->getId()))
		{
			/** @var MultiLanguageDriverInterface $dataProvider */
			$this
				->addToTemplate('languages', $environment->getController()->getSupportedLanguages($objDBModel->getId()), $template)
				->addToTemplate('currentLanguage', $dataProvider->getCurrentLanguage(), $template)
				->addToTemplate('languageSubmit', specialchars($translator->translate('MSC.showSelected')), $template)
				->addToTemplate('backBT', specialchars($translator->translate('MSC.backBT')), $template);
		}
		else
		{
			$this->addToTemplate('language', null, $template);
		}

		return $template->parse();
	}

	/**
	 * Show all entries from one table.
	 *
	 * @return string
	 */
	public function showAll()
	{
		return sprintf(
			$this->notImplMsg,
			'showAll - Mode ' . $this->getViewSection()->getListingConfig()->getGroupingMode()
		);
	}

	/**
	 * Generates a sub palette for the given selector (field name).
	 *
	 * @param string $strSelector The name of the selector field.
	 *
	 * @return string
	 */
	public function generateAjaxPalette($strSelector)
	{
		return vsprintf($this->notImplMsg, 'generateAjaxPalette');
	}

	/**
	 * Generate all buttons for the header of a view.
	 *
	 * @param string $strButtonId The id for the surrounding html div element.
	 *
	 * @return string
	 */
	protected function generateHeaderButtons($strButtonId)
	{
		$environment        = $this->getEnvironment();
		$definition         = $environment->getDataDefinition();
		$listingConfig      = $this->getViewSection()->getListingConfig();
		$providerName       = $environment->getDataDefinition()->getName();
		$parentProviderName = $environment->getDataDefinition()->getName();
		$arrReturn          = array();
		$globalOperations   = $this->getViewSection()->getGlobalCommands();
		$config             = $this->getEnvironment()->getController()->getBaseConfig();

		$this->getPanel()->initialize($config);

		$sorting = $config->getSorting();

		if (!is_array($globalOperations))
		{
			$globalOperations = array();
		}

		// Make Urls absolute.
		foreach ($globalOperations as $k => $v)
		{
			/** @var AddToUrlEvent $event */
			$event = $environment->getEventPropagator()->propagate(
				ContaoEvents::BACKEND_ADD_TO_URL,
				new AddToUrlEvent(
					$v['href']
				)
			);

			$globalOperations[$k]['href'] = $event->getUrl();
		}

		// Special case - if select mode active, we must not display the "edit all" button.
		if ($this->isSelectModeActive())
		{
			unset($globalOperations['all']);
		}
		// We have the select mode.
		else
		{
			$addButton = false;

			$basicDefinition = $definition->getBasicDefinition();
			$pid             = $environment->getInputProvider()->getParameter('pid');
			$strHref         = '';
			$mode            = $basicDefinition->getMode();

			if (($mode == BasicDefinitionInterface::MODE_FLAT)
				|| (($mode == BasicDefinitionInterface::MODE_PARENTEDLIST) && !$sorting))
			{
				// Add new button.
				if (strlen($parentProviderName))
				{
					/** @var AddToUrlEvent $event */
					$event = $environment->getEventPropagator()->propagate(
						ContaoEvents::BACKEND_ADD_TO_URL,
						new AddToUrlEvent(
							'&amp;act=create' .
							($pid ? '&amp;pid=' . $pid : '')
						)
					);
				}
				else
				{
					/** @var AddToUrlEvent $event */
					$event = $environment->getEventPropagator()->propagate(
						ContaoEvents::BACKEND_ADD_TO_URL,
						new AddToUrlEvent('act=create')
					);
				}

				$strHref   = $event->getUrl();
				$addButton = !$basicDefinition->isClosed();
			}
			elseif(($mode == BasicDefinitionInterface::MODE_PARENTEDLIST)
				|| ($mode == BasicDefinitionInterface::MODE_HIERARCHICAL))
			{
				/** @var AddToUrlEvent $event */
				$event = $environment->getEventPropagator()->propagate(
					ContaoEvents::BACKEND_ADD_TO_URL,
					new AddToUrlEvent(
						'&amp;act=paste&amp;mode=create' .
						($pid ? '&amp;pid=' . $pid : '')
					)
				);

				$strHref = $event->getUrl();

				$addButton = !($basicDefinition->isClosed() || $environment->getClipboard()->isNotEmpty());
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
							'title'      => $this->translate('new.1', $providerName),
							'label'      => $this->translate('new.0', $providerName)
						)
					),
					$globalOperations
				);
			}

		}

		// Add clear clipboard button if needed.
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			/** @var AddToUrlEvent $event */
			$event = $environment->getEventPropagator()->propagate(
				ContaoEvents::BACKEND_ADD_TO_URL,
				new AddToUrlEvent('clipboard=1')
			);

			$globalOperations = array_merge(
				array(
					'button_clipboard'     => array
					(
						'class'      => 'header_clipboard',
						'accesskey'  => 'x',
						'href'       => $event->getUrl(),
						'title'      => $this->translate('MSC.clearClipboard'),
						'label'      => $this->translate('MSC.clearClipboard')
					)
				),
				$globalOperations
			);
		}

		// Add back button.
		if ($this->isSelectModeActive() || $parentProviderName)
		{
			/** @var GetReferrerEvent $event */
			$event = $environment->getEventPropagator()->propagate(
				ContaoEvents::SYSTEM_GET_REFERRER,
				new GetReferrerEvent(true, $parentProviderName)
			);

			$globalOperations = array_merge(
				array(
					'back_button'    => array
					(
						'class'      => 'header_back',
						'accesskey'  => 'b',
						'href'       => $event->getReferrerUrl(),
						'attributes' => 'onclick="Backend.getScrollOffset();"',
						'title'      => $this->translate('MSC.backBT'),
						'label'      => $this->translate('MSC.backBT')
					)
				),
				$globalOperations
			);
		}

		// Add global buttons.
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

			$this->getEnvironment()->getEventPropagator()->propagate(
				$buttonEvent::NAME,
				$buttonEvent,
				array(
					$this->getEnvironment()->getDataDefinition()->getName(),
					$k
				)
			);

			// Allow to override the button entirely.
			$html = $buttonEvent->getHtml();
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
		$buttonsEvent->setButtons($arrReturn);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$buttonsEvent::NAME,
			$buttonsEvent,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);

		return '<div id="' . $strButtonId . '">' . implode(' &nbsp; :: &nbsp; ', $buttonsEvent->getButtons()) . '</div>';
	}

	/**
	 * Render a command button.
	 *
	 * @param CommandInterface $objCommand           The command to render the button for.
	 *
	 * @param ModelInterface   $objModel             The model to which the command shall get applied.
	 *
	 * @param bool             $blnCircularReference Determinator if there exists a circular reference between the model
	 *                                               and the model(s) contained in the clipboard.
	 *
	 * @param array            $arrChildRecordIds    List of the ids of all child models of the current model.
	 *
	 * @param ModelInterface   $previous             The previous model in the collection.
	 *
	 * @param ModelInterface   $next                 The next model in the collection.
	 *
	 * @return string
	 */
	protected function buildCommand($objCommand, $objModel, $blnCircularReference, $arrChildRecordIds, $previous, $next)
	{
		$propagator = $this->getEnvironment()->getEventPropagator();

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
			$title = sprintf($opDesc, $objModel->getID());
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

		$arrParameters = (array)$objCommand->getParameters();

		// Cut needs some special information.
		if ($objCommand->getName() == 'cut')
		{
			// Get data provider from current and parent.
			$strParentDataProvider = $objModel->getMeta(DCGE::MODEL_PTABLE);
			$arrParameters['cdp']  = $objModel->getProviderName();

			// Add parent provider if exists.
			if ($strParentDataProvider != null)
			{
				$arrParameters['pdp'] = $strParentDataProvider;
			}

			// If we have a id add it, used for mode 4 and all parent -> current views.
			if ($this->getEnvironment()->getInputProvider()->hasParameter('id'))
			{
				$arrParameters['id'] = $this->getEnvironment()->getInputProvider()->getParameter('id');
			}

			// Source is the id of the element which should move.
			$arrParameters['source'] = $objModel->getID();
		}
		else
		{
			// TODO: Shall we interface this option?
			$idParam = $objCommand->getExtra()['idparam'];
			if ($idParam)
			{
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

		/** @var AddToUrlEvent $event */
		$event = $propagator->propagate(
			ContaoEvents::BACKEND_ADD_TO_URL,
			new AddToUrlEvent($strHref)
		);

		$strHref = $event->getUrl();

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
			->setPrevious($previous)
			->setNext($next);

		$propagator->propagate(
			$buttonEvent::NAME,
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

		$extra = $objCommand->getExtra();
		$icon  = $extra['icon'];

		if ($objCommand->isDisabled())
		{
			/** @var GenerateHtmlEvent $event */
			$event = $propagator->propagate(
				ContaoEvents::IMAGE_GET_HTML,
				new GenerateHtmlEvent(
					substr_replace($icon, '_1', strrpos($icon, '.'), 0),
					$buttonEvent->getLabel()
				)
			);

			return $event->getHtml();
		}

		/** @var GenerateHtmlEvent $event */
		$event = $propagator->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent(
				$icon,
				$buttonEvent->getLabel()
			)
		);

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$buttonEvent->getHref(),
			specialchars($buttonEvent->getTitle()),
			$buttonEvent->getAttributes(),
			$event->getHtml()
		);
	}

	/**
	 * Render the paste into button.
	 *
	 * @param GetPasteButtonEvent $event The event that has been triggered.
	 *
	 * @return string
	 */
	public function renderPasteIntoButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteInto()))
		{
			return $event->getHtmlPasteInto();
		}

		$strLabel = $this->translate('pasteinto.0', $event->getModel()->getProviderName());
		if ($event->isPasteIntoDisabled())
		{
			/** @var GenerateHtmlEvent $imageEvent */
			$imageEvent = $this->getEnvironment()->getEventPropagator()->propagate(
				ContaoEvents::IMAGE_GET_HTML,
				new GenerateHtmlEvent(
					'pasteinto_.gif',
					$strLabel,
					'class="blink"'
				)
			);

			return $imageEvent->getHtml();
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $this->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent(
				'pasteinto.gif',
				$strLabel,
				'class="blink"'
			)
		);

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
				$event->getHrefInto(),
				specialchars($strLabel),
				'onclick="Backend.getScrollOffset()"',
				$imageEvent->getHtml()
			);
	}

	/**
	 * Render the paste after button.
	 *
	 * @param GetPasteButtonEvent $event The event that has been triggered.
	 *
	 * @return string
	 */
	public function renderPasteAfterButton(GetPasteButtonEvent $event)
	{
		if (!is_null($event->getHtmlPasteAfter()))
		{
			return $event->getHtmlPasteAfter();
		}

		$strLabel = $this->translate('pasteafter.0', $event->getModel()->getProviderName());
		if ($event->isPasteAfterDisabled())
		{
			/** @var GenerateHtmlEvent $imageEvent */
			$imageEvent = $this->getEnvironment()->getEventPropagator()->propagate(
				ContaoEvents::IMAGE_GET_HTML,
				new GenerateHtmlEvent(
					'pasteafter_.gif',
					$strLabel,
					'class="blink"'
				)
			);

			return $imageEvent->getHtml();
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $this->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent(
				'pasteafter.gif',
				$strLabel,
				'class="blink"'
			)
		);

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$event->getHrefAfter(),
			specialchars($strLabel),
			'onclick="Backend.getScrollOffset()"',
			$imageEvent->getHtml()
		);
	}

	/**
	 * Compile buttons from the table configuration array and return them as HTML.
	 *
	 * @param ModelInterface $objModelRow          The model for which the buttons shall be generated for.
	 * @param string         $strTable             The name of the data definition (unused).
	 * @param array          $arrRootIds           The root ids (unused).
	 * @param boolean        $blnCircularReference The ids building a circular reference (unused).
	 * @param array          $arrChildRecordIds    The ids of all child records of the model (unused).
	 * @param ModelInterface $previous             The previous model in the collection.
	 * @param ModelInterface $next                 The next model in the collection.
	 * @return string
	 */
	protected function generateButtons(
		ModelInterface $objModelRow,
		$strTable,
		$arrRootIds = array(),
		$blnCircularReference = false,
		$arrChildRecordIds = null,
		$previous = null,
		$next = null
	)
	{
		$commands     = $this->getViewSection()->getModelCommands();
		$objClipboard = $this->getEnvironment()->getClipboard();
		$propagator   = $this->getEnvironment()->getEventPropagator();

		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$circularIds = $objClipboard->getCircularIds();
			$isCircular  = in_array($objModelRow->getID(), $circularIds);
		}
		else
		{
			$circularIds = array();
			$isCircular  = false;
		}

		$arrButtons = array();
		foreach ($commands->getCommands() as $command)
		{
			$arrButtons[$command->getName()] = $this->buildCommand(
				$command,
				$objModelRow,
				$isCircular,
				$circularIds,
				$previous,
				$next
			);
		}

		// Add paste into/after icons.
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{

			$strMode = $objClipboard->getMode();

			// Add ext. information.
			$strAdd2UrlAfter = sprintf('act=%s&amp;after=%s&amp;',
				$strMode,
				$objModelRow->getID()
			);

			$strAdd2UrlInto = sprintf('act=%s&amp;into=%s&amp;',
				$strMode,
				$objModelRow->getID()
			);

			/** @var AddToUrlEvent $urlAfter */
			$urlAfter = $propagator->propagate(
				ContaoEvents::BACKEND_ADD_TO_URL,
				new AddToUrlEvent($strAdd2UrlAfter)
			);

			/** @var AddToUrlEvent $urlInto */
			$urlInto = $propagator->propagate(
				ContaoEvents::BACKEND_ADD_TO_URL,
				new AddToUrlEvent($strAdd2UrlInto)
			);

			$buttonEvent = new GetPasteButtonEvent($this->getEnvironment());
			$buttonEvent
				->setModel($objModelRow)
				->setCircularReference($isCircular)
				->setPrevious($previous)
				->setNext($next)
				->setHrefAfter($urlAfter->getUrl())
				->setHrefInto($urlInto->getUrl())
				// Check if the id is in the ignore list.
				->setPasteAfterDisabled($objClipboard->isCut() && $isCircular)
				->setPasteIntoDisabled($objClipboard->isCut() && $isCircular);

			$this->getEnvironment()->getEventPropagator()->propagate(
				$buttonEvent::NAME,
				$buttonEvent,
				array($this->getEnvironment()->getDataDefinition()->getName())
			);

			$arrButtons['pasteafter'] = $this->renderPasteAfterButton($buttonEvent);
			if ($this->getDataDefinition()->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL)
			{
				$arrButtons['pasteinto'] = $this->renderPasteIntoButton($buttonEvent);
			}

		}

		return implode(' ', $arrButtons);
	}

	/**
	 * Render the panel.
	 *
	 * @return string
	 *
	 * @throws DcGeneralRuntimeException When no panel has been defined.
	 */
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

	/**
	 * Get the breadcrumb navigation via event.
	 *
	 * @return string
	 */
	protected function breadcrumb()
	{
		$event = new GetBreadcrumbEvent($this->getEnvironment());

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);

		$arrReturn = $event->getElements();

		if (!is_array($arrReturn) || count($arrReturn) == 0)
		{
			return null;
		}

		$GLOBALS['TL_CSS'][] = 'system/modules/generalDriver/html/css/generalBreadcrumb.css';

		$objTemplate = $this->getTemplate('dcbe_general_breadcrumb');
		$this->addToTemplate('elements', $arrReturn, $objTemplate);

		return $objTemplate->parse();
	}

	/**
	 * Process input and return all modified properties or null if there is no input.
	 *
	 * @param ContaoWidgetManager $widgetManager The widget manager in use.
	 *
	 * @return null|PropertyValueBag
	 */
	public function processInput($widgetManager)
	{
		$input = $this->getEnvironment()->getInputProvider();

		if ($input->getValue('FORM_SUBMIT') == $this->getEnvironment()->getDataDefinition()->getName())
		{
			$propertyValues = new PropertyValueBag();
			$propertyNames  = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getPropertyNames();

			// Process input and update changed properties.
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
	 * Format a model accordingly to the current configuration.
	 *
	 * Returns either an array when in tree mode or a string in (parented) list mode.
	 *
	 * @param ModelInterface $model The model that shall be formatted.
	 *
	 * @return array
	 */
	public function formatModel(ModelInterface $model)
	{
		$listing      = $this->getViewSection()->getListingConfig();
		$properties   = $this->getDataDefinition()->getPropertiesDefinition();
		$formatter    = $listing->getLabelFormatter($model->getProviderName());
		$sorting      = array_keys((array)$listing->getDefaultSortingFields());
		$firstSorting = reset($sorting);

		$args = array();
		foreach ($formatter->getPropertyNames() as $propertyName)
		{
			if ($properties->hasProperty($propertyName))
			{
				$property = $properties->getProperty($propertyName);

				$args[$propertyName] = (string)$this->getReadableFieldValue($property, $model, $model->getProperty($propertyName));
			}
			else
			{
				$args[$propertyName] = '-';
			}

		}

		$event = new ModelToLabelEvent($this->getEnvironment(), $model);
		$event
			->setArgs($args)
			->setLabel($formatter->getFormat())
			->setFormatter($formatter);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array($this->getEnvironment()->getDataDefinition()->getName())
		);

		$arrLabel = array();

		// Add columns.
		if ($listing->getShowColumns())
		{
			$fields = $formatter->getPropertyNames();
			$args   = $event->getArgs();

			if (!is_array($args))
			{
				$arrLabel[] = array(
					'colspan' => count($fields),
					'class' => 'tl_file_list col_1',
					'content' => $args
				);
			}
			else
			{
				foreach ($fields as $j => $propertyName)
				{
					$arrLabel[] = array(
						'colspan' => 1,
						'class' => 'tl_file_list col_' . $j . (($propertyName == $firstSorting) ? ' ordered_by' : ''),
						'content' => (($args[$propertyName] != '') ? $args[$propertyName] : '-')
					);
				}
			}
		}
		else
		{
			if (!is_array($event->getArgs()))
			{
				$string = $event->getArgs();
			}
			else
			{
				$string = vsprintf($event->getLabel(), $event->getArgs());
			}

			if ($formatter->getMaxLength() !== null && strlen($string) > $formatter->getMaxLength())
			{
				$string = substr($string, 0, $formatter->getMaxLength());
			}

			$arrLabel[] = array(
				'colspan' => null,
				'class'   => 'tl_file_list',
				'content' => $string
			);
		}

		return $arrLabel;
	}

	/**
	 * Get for a field the readable value.
	 *
	 * @param PropertyInterface $property The property to be rendered.
	 *
	 * @param ModelInterface    $model    The model from which the property value shall be retrieved from.
	 *
	 * @param mixed             $value    The value for the property.
	 *
	 * @return mixed
	 */
	public function getReadableFieldValue(PropertyInterface $property, ModelInterface $model, $value)
	{
		$event = new RenderReadablePropertyValueEvent($this->getEnvironment(), $model, $property, $value);
		$this->getEnvironment()->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$this->getEnvironment()->getDataDefinition()->getName(),
				$property->getName()
			)
		);

		if ($event->getRendered() !== null)
		{
			return $event->getRendered();
		}

		return $value;
	}
}
