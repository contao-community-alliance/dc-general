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

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

class ParentView extends BaseView
{
	/**
	 * Load the collection of child items and the parent item for the currently selected parent item.
	 *
	 * Consumes input parameter "id".
	 *
	 * @return CollectionInterface
	 *
	 * @throws DcGeneralRuntimeException
	 */
	public function loadCollection()
	{
		$environment = $this->getEnvironment();

		// Setup
		$objCurrentDataProvider = $environment->getDataProvider();

		$objChildConfig = $environment->getController()->getBaseConfig();

		if (!$objChildConfig->getSorting())
		{
			$objChildConfig->setSorting($this->getViewSection()->getListingConfig()->getDefaultSortingFields());
		}

		$this->getPanel()->initialize($objChildConfig);

		$objChildCollection = $objCurrentDataProvider->fetchAll($objChildConfig);

		return $objChildCollection;
	}

	/**
	 * Load the parent model for the current list.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	protected function loadParentModel()
	{
		$environment = $this->getEnvironment();

		if (!($parentId = $environment->getInputProvider()->getParameter('id')))
		{
			throw new DcGeneralRuntimeException('ParentView needs a proper parent id defined, somehow none is defined?', 1);
		}

		if (!($objParentProvider = $environment->getDataProvider($environment->getDataDefinition()->getBasicDefinition()->getParentDataProvider())))
		{
			throw new DcGeneralRuntimeException("ParentView needs a proper parent data provider defined, somehow none is defined?", 1);
		}

		$objParentItem = $objParentProvider->fetch($objParentProvider->getEmptyConfig()->setId($parentId));

		if (!$objParentItem)
		{
			// No parent item found, might have been deleted - we transparently create it for our filter to be able to filter to nothing.
			// TODO: shall we rather bail with "parent not found"?
			$objParentItem = $objParentProvider->getEmptyModel();
			$objParentItem->setID($parentId);
		}

		return $objParentItem;
	}

	/**
	 * Render the entries for parent view.
	 *
	 * @param CollectionInterface $collection
	 *
	 * @param array               $groupingInformation
	 */
	protected function renderEntries($collection, $groupingInformation)
	{
		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();
		$view        = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
		/** @var Contao2BackendViewDefinitionInterface $view */
		$listing     = $view->getListingConfig();
		$remoteCur   = null;
		$groupclass  = 'tl_folder_tlist';
		$eoCount     = -1;

		$objConfig = $environment->getDataProvider()->getEmptyConfig();
		$this->getPanel()->initialize($objConfig);
		$firstSorting = $objConfig->getSorting() ? reset($objConfig->getSorting()) : '';

		$strGroup = '';

		// Run each model
		$i = 0;
		foreach ($collection as $model)
		{
			/** @var ModelInterface $model */
			$i++;

			// Add the group header
			if ($groupingInformation)
			{
				$remoteNew = $this->formatCurrentValue($groupingInformation['property'], $model, $groupingInformation['mode'], $groupingInformation['length']);

				// Add the group header if it differs from the last header.
				if (!$listing->getShowColumns() && ($groupingInformation['mode'] !== ListingConfigInterface::GROUP_NONE) && (($remoteNew != $remoteCur) || ($remoteCur === null)))
				{
					$eoCount = -1;

					$model->setMeta(DCGE::MODEL_GROUP_VALUE, array(
						'class' => $groupclass,
						'value' => $remoteNew
					));

					$groupclass = 'tl_folder_list';
					$remoteCur = $remoteNew;
				}
			}

			if ($listing->getItemCssClass())
			{
				$model->setMeta(DCGE::MODEL_CLASS, $listing->getItemCssClass());
			}

			// Regular buttons
			if (!$this->isSelectModeActive())
			{
				$strPrevious = ((!is_null($collection->get($i - 1))) ? $collection->get($i - 1)->getID() : null);
				$strNext = ((!is_null($collection->get($i + 1))) ? $collection->get($i + 1)->getID() : null);

				$buttons = $this->generateButtons($model, $definition->getName(), $environment->getRootIds(), false, null, $strPrevious, $strNext);

				$model->setMeta(DCGE::MODEL_BUTTONS, $buttons);
			}

			$event = new ParentViewChildRecordEvent($this->getEnvironment(), $model);

			$this->getEnvironment()->getEventPropagator()->propagate(
				$event,
				array(
					$this->getEnvironment()->getDataDefinition()->getName(),
					$model->getId()
				)
			);

			if ($event->getHtml() !== null)
			{
				$information = array(
					array(
						'colspan' => 1,
						'class'   => 'tl_file_list col_1',
						'content' => $event->getHtml()
					)
				);
				$model->setMeta(DCGE::MODEL_LABEL_VALUE, $information);
			}
			else
			{
				$model->setMeta(DCGE::MODEL_LABEL_VALUE, $this->formatModel($model));
			}
		}
	}

	/**
	 * Render the header of the parent view with information
	 * from the parent table
	 *
	 * @param ModelInterface $parentModel
	 *
	 * @return array
	 */
	protected function renderFormattedHeaderFields($parentModel)
	{
		$environment       = $this->getEnvironment();
		$definition        = $environment->getDataDefinition();
		/** @var Contao2BackendViewDefinitionInterface $viewDefinition */
		$viewDefinition    = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
		$listingDefinition = $viewDefinition->getListingConfig();
		$headerFields      = $listingDefinition->getHeaderPropertyNames();
		$parentDefinition  = $environment->getParentDataDefinition();
		$parentName        = $definition->getBasicDefinition()->getParentDataProvider();
		$add               = array();

		foreach ($headerFields as $v)
		{
			$_v = deserialize($parentModel->getProperty($v));

			if ($v == 'tstamp')
			{
				$_v = date($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
			}

			// FIXME: enable again when parent definition is available.
			$property = $parentDefinition->getPropertiesDefinition()->getProperty($v);

			// FIXME: foreignKey is not implemented yet.
			if ($property && (($v != 'tstamp')/* || $property->get('foreignKey')*/))
			{
				$evaluation = $property->getExtra();
				// FIXME: reference is not implemented yet.
				// $reference  = $property->get('reference');
				$options    = $property->getOptions();

				if (is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple'])
				{
					$_v = strlen($_v) ? $this->translate('yes', 'MSC') : $this->translate('no', 'MSC');
				}
				elseif ($_v && $evaluation['rgxp'] == 'date')
				{
					$_v = BackendBindings::parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $_v);
				}
				elseif ($_v && $evaluation['rgxp'] == 'time')
				{
					$_v = BackendBindings::parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $_v);
				}
				elseif ($_v && $evaluation['rgxp'] == 'datim')
				{
					$_v = BackendBindings::parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
				}
				elseif (is_array($reference[$_v]))
				{
					$_v = $reference[$_v][0];
				}
				elseif (isset($reference[$_v]))
				{
					$_v = $reference[$_v];
				}
				elseif ($evaluation['isAssociative'] || array_is_assoc($options))
				{
					$_v = $options[$_v];
				}
			}

			// Add the sorting field
			if ($_v != '')
			{
				$lang = $this->translate(sprintf('%s.0',  $v), $parentName);
				$key = $lang ? $lang : $v;
				$add[$key] = $_v;
			}
		}

		$event = new GetParentHeaderEvent($environment);
		$event->setAdditional($add);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event,
			$this->getEnvironment()->getDataDefinition()->getName()
		);

		if (!$event->getAdditional() !== null)
		{
			$add = $event->getAdditional();
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
	 * Retrieve a list of html buttons to use in the top panel (submit area).
	 *
	 * @param ModelInterface $parentModel
	 *
	 * @return array
	 */
	protected function getHeaderButtons($parentModel)
	{
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$clipboard        = $environment->getClipboard();
		$basicDefinition  = $definition->getBasicDefinition();
		$parentDefinition = $environment->getParentDataDefinition();
		$parentName       = $basicDefinition->getParentDataProvider();

		$headerButtons = array();
		if ($this->isSelectModeActive())
		{
			$headerButtons['selectAll'] = sprintf(
				'<label for="tl_select_trigger" class="tl_select_label">%s</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">',
				$this->translate('selectAll', 'MSC')
			);
		}
		else
		{
			$objConfig = $this->getEnvironment()->getController()->getBaseConfig();
			$this->getPanel()->initialize($objConfig);
			$strSorting = $objConfig->getSorting();

			if (($strSorting !== null)
				&& !$basicDefinition->isClosed()
				&& $basicDefinition->isCreatable())
			{
				$headerButtons['pasteNew'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					// TODO: why the same id in both, id and pid?
					BackendBindings::addToUrl('act=create&amp;mode=2&amp;pid=' . $parentModel->getID() . '&amp;id=' . $parentModel->getID()),
					specialchars($this->translate('pastenew.1', $definition->getName())),
					BackendBindings::generateImage('new.gif', $this->translate('pastenew.0', $definition->getName()))
				);
			}

			if ($parentDefinition && $parentDefinition->getBasicDefinition()->isEditable())
			{
				$headerButtons['editHeader'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					preg_replace('/&(amp;)?table=[^& ]*/i', ($parentName ? '&amp;table=' . $parentName : ''), BackendBindings::addToUrl('act=edit')),
					specialchars($this->translate('editheader.1', $definition->getName())),
					BackendBindings::generateImage('edit.gif', $this->translate('editheader.0', $definition->getName()))
				);
			}

			if ($clipboard->isNotEmpty())
			{
				$headerButtons['pasteAfter'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					BackendBindings::addToUrl('act=' . $clipboard->getMode() . '&amp;mode=2&amp;pid=' . $parentModel->getID()),
					specialchars($this->translate('pasteafter.1', $definition->getName())),
					BackendBindings::generateImage('pasteafter.gif', $this->translate('pasteafter.0', $definition->getName()), 'class="blink"')
				);
			}
		}

		return implode(' ', $headerButtons);
	}


	/**
	 * Show parent view mode 4.
	 *
	 * @param CollectionInterface $collection
	 *
	 * @param ModelInterface      $parentModel
	 *
	 * @return string HTML output
	 */
	protected function viewParent($collection, $parentModel)
	{
		$definition          = $this->getEnvironment()->getDataDefinition();
		$parentProvider      = $definition->getBasicDefinition()->getParentDataProvider();
		$groupingInformation = $this->getGroupingMode();


		// Skip if we have no parent or parent collection.
		if (!$parentModel)
		{
			BackendBindings::log(
				sprintf(
					'The view for %s has either a empty parent data provider or collection.',
					$parentProvider
				),
				__CLASS__ . ' ' . __FUNCTION__ . '()',
				TL_ERROR
			);
			BackendBindings::redirect('contao/main.php?act=error');
		}

		// Load language file and data container array of the parent table
		BackendBindings::loadLanguageFile($parentProvider);
		BackendBindings::loadDataContainer($parentProvider);

		// Add template
		$objTemplate = $this->getTemplate('dcbe_general_parentView');

		$this
			->addToTemplate('tableName', strlen($definition->getName())? $definition->getName() : 'none', $objTemplate)
			->addToTemplate('collection', $collection, $objTemplate)
			->addToTemplate('select', $this->isSelectModeActive(), $objTemplate)
			->addToTemplate('action', ampersand(\Environment::getInstance()->request, true), $objTemplate)
			->addToTemplate('header', $this->renderFormattedHeaderFields($parentModel), $objTemplate)
			->addToTemplate('hasSorting', ($groupingInformation['property'] == 'sorting'), $objTemplate)
			->addToTemplate('mode', ($groupingInformation ? $groupingInformation['mode'] : null), $objTemplate)
			->addToTemplate('pdp', (string)$parentProvider, $objTemplate)
			->addToTemplate('cdp', $definition->getName(), $objTemplate)
			->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate)
			->addToTemplate('headerButtons', $this->getHeaderButtons($parentModel), $objTemplate);

		$this->renderEntries($collection, $groupingInformation);

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$this->addToTemplate('breadcrumb', $strBreadcrumb, $objTemplate);
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
		$this->checkClipboard();
		$collection  = $this->loadCollection();
		$parentModel = $this->loadParentModel();

		$arrReturn            = array();
		$arrReturn['panel']   = $this->panel();
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewParent($collection, $parentModel);

		return implode("\n", $arrReturn);
	}
}
