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

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Data\DCGE;
use DcGeneral\View\DefaultView\Events\GetParentHeaderEvent;
use DcGeneral\View\DefaultView\Events\ParentViewChildRecordEvent;

class ParentView extends BaseView
{
	/**
	 * Load the collection of child items and the parent item for the currently selected parent item.
	 *
	 * Consumes input parameter "id".
	 *
	 * @return BaseView
	 *
	 * @throws \RuntimeException
	 */
	public function loadCollection()
	{
		$environment = $this->getEnvironment();

		if (!($parentId = $environment->getInputProvider()->getParameter('id')))
		{
			throw new \RuntimeException("mode 4 need a proper parent id defined, somehow none is defined?", 1);
		}

		if (!($objParentProvider = $environment->getDataDriver($environment->getDataDefinition()->getParentDriverName())))
		{
			throw new \RuntimeException("mode 4 need a proper parent data provider defined, somehow none is defined?", 1);
		}

		// Setup
		$objCurrentDataProvider = $environment->getDataDriver();

		$objChildConfig = $environment->getController()->getBaseConfig();
		$environment->getPanelContainer()->initialize($objChildConfig);

		$objChildCollection = $objCurrentDataProvider->fetchAll($objChildConfig);

		$environment->setCurrentCollection($objChildCollection);

		$objParentItem = $objParentProvider->fetch($objParentProvider->getEmptyConfig()->setId($parentId));

		if (!$objParentItem)
		{
			// No parent item found, might have been deleted - we transparently create it for our filter to be able to filter to nothing.
			// TODO: shall we rather bail with "parent not found"?
			$objParentItem = $objParentProvider->getEmptyModel();
			$objParentItem->setID($parentId);
		}

		$objParentCollection = $objParentProvider->getEmptyCollection();
		$objParentCollection->add($objParentItem);
		$environment->setCurrentParentCollection($objParentCollection);

		return $this;
	}

	/**
	 * Render the entries for parent view.
	 */
	protected function renderEntries()
	{
		$definition   = $this->getEnvironment()->getDataDefinition();
		$firstSorting = $definition->getFirstSorting();

		$strGroup = '';

		// Run each model
		for ($i = 0; $i < $this->getCurrentCollection()->length(); $i++)
		{
			// Get model
			$objModel = $this->getCurrentCollection()->get($i);

			// Set in DC as current for callback and co.
			// TODO: should be obsolete for event based stuff, IMO.
			$this->getEnvironment()->setCurrentModel($objModel);

			// Decrypt encrypted value
			// FIXME: this has to be done somewhere else... it is bullshit to hard decrypt here and store it in the model.
/*
			foreach ($objModel as $k => $v)
			{
				if ($this->getDataDefinition()->getProperty($k)->isEncrypted())
				{
					$v = deserialize($v);

					$objModel->setProperty($k, \Encryption::getInstance()->decrypt($v));
				}
			}
*/
			// Add the group header
			if (!$definition->isGroupingDisabled() && $firstSorting != 'sorting')
			{
				// get a list with all fields for sorting
				$orderBy = $definition->getAdditionalSorting();

				// Default ASC
				if (count($orderBy) == 0)
				{
					$sortingMode = 9;
				}
				// If the current First sorting is the default one use the global flag
				else if ($firstSorting == $orderBy[0])
				{
					$sortingMode = $definition->getSortingFlag();
				}
				// Use the field flag, if given
				else if ($definition->getProperty($firstSorting)->getSortingFlag() != '')
				{
					$sortingMode = $definition->getProperty($firstSorting)->getSortingFlag();
				}
				// Use the global as fallback
				else
				{
					$sortingMode = $definition->getSortingFlag();
				}

				$remoteNew = $this->formatCurrentValue($firstSorting, $objModel->getProperty($firstSorting), $sortingMode);
				$group = $this->formatGroupHeader($firstSorting, $remoteNew, $sortingMode, $objModel);

				if ($group != $strGroup)
				{
					$strGroup = $group;
					$objModel->setMeta(DCGE::MODEL_GROUP_HEADER, $group);
				}
			}

			$objModel->setMeta(DCGE::MODEL_CLASS, ($this->getDC()->arrDCA['list']['sorting']['child_record_class'] != '') ? ' ' . $this->getDC()->arrDCA['list']['sorting']['child_record_class'] : '');

			// Regular buttons
			if (!$this->isSelectModeActive())
			{
				$strPrevious = ((!is_null($this->getCurrentCollection()->get($i - 1))) ? $this->getCurrentCollection()->get($i - 1)->getID() : null);
				$strNext = ((!is_null($this->getCurrentCollection()->get($i + 1))) ? $this->getCurrentCollection()->get($i + 1)->getID() : null);

				$buttons = $this->generateButtons($objModel, $this->getDataDefinition()->getName(), $this->getDC()->getEnvironment()->getRootIds(), false, null, $strPrevious, $strNext);

				$objModel->setMeta(DCGE::MODEL_BUTTONS, $buttons);
			}

			$event = new ParentViewChildRecordEvent();

			$event
				->setEnvironment($this->getEnvironment())
				->setModel($objModel);

			$this->dispatchEvent(ParentViewChildRecordEvent::NAME, $event);

			if ($event->getHtml() !== null)
			{
				$objModel->setMeta(DCGE::MODEL_LABEL_VALUE, $event->getHtml());
			}
		}
	}

	/**
	 * Render the header of the parent view with information
	 * from the parent table
	 *
	 * @return array
	 */
	protected function renderFormattedHeaderFields()
	{
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$headerFields     = $definition->getParentViewHeaderProperties();
		$parentDefinition = $environment->getParentDataDefinition();
		$parentName       = $definition->getParentDriverName();
		$add              = array();

		foreach ($headerFields as $v)
		{
			$_v = deserialize($environment->getCurrentParentCollection()->get(0)->getProperty($v));

			if ($v == 'tstamp')
			{
				$_v = date($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
			}

			$property = $parentDefinition->getProperty($v);

			if ($property && ($v != 'tstamp' || $property->get('foreignKey')))
			{
				$evaluation = $property->getEvaluation();
				$reference  = $property->get('reference');
				$options    = $property->get('options');

				if (is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif ($property->get('inputType') == 'checkbox' && !$evaluation['multiple'])
				{
					$_v = strlen($_v) ? $this->translate('MSC/yes') : $this->translate('MSC/no');
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
				$lang = $this->translate(sprintf('%s/%s/0', $parentName,  $v));
				$key = $lang ? $lang : $v;
				$add[$key] = $_v;
			}
		}

		$event = new GetParentHeaderEvent();
		$event
			->setEnvironment($environment)
			->setAdditional($add);

		$this->dispatchEvent(GetParentHeaderEvent::NAME, $event);

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
	 * Retrieve a list of html buttons to use in the bottom panel (submit area).
	 *
	 * @return array()
	 */
	protected function getHeaderButtons()
	{
		$definition       = $this->getEnvironment()->getDataDefinition();
		$clipboard        = $this->getEnvironment()->getClipboard();
		$parentDefinition = $this->getEnvironment()->getParentDataDefinition();
		$parentCollection = $this->getEnvironment()->getCurrentParentCollection();

		// Add parent provider if exists
		if ($definition->getParentDriverName() != null)
		{
			$strPDP = $definition->getParentDriverName();
		}
		else
		{
			$strPDP = '';
		}

		$headerButtons = array();
		if ($this->isSelectModeActive())
		{
			$headerButtons['selectAll'] = sprintf(
				'<label for="tl_select_trigger" class="tl_select_label">%s</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">',
				$this->translate('MSC/selectAll')
			);
		}
		else
		{
			$objConfig = $this->getEnvironment()->getController()->getBaseConfig();
			$this->getDC()->getEnvironment()->getPanelContainer()->initialize($objConfig);
			$strSorting = $objConfig->getSorting();

			if (($strSorting !== null)
				&& !$definition->isClosed()
				&& $definition->isCreatable())
			{
				$headerButtons['pasteNew'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					// TODO: why the same id in both, id and pid?
					BackendBindings::addToUrl('act=create&amp;mode=2&amp;pid=' . $parentCollection->get(0)->getID() . '&amp;id=' . $parentCollection->get(0)->getID()),
					specialchars($this->translate($definition->getName() . '/pastenew/1')),
					BackendBindings::generateImage('new.gif', $this->translate($definition->getName() . '/pastenew/0'))
				);
			}

			if ($parentDefinition->isEditable())
			{
				$headerButtons['editHeader'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					preg_replace('/&(amp;)?table=[^& ]*/i', ($strPDP ? '&amp;table=' . $strPDP : ''), BackendBindings::addToUrl('act=edit')),
					specialchars($this->translate($definition->getName() . '/editheader/1')),
					BackendBindings::generateImage('edit.gif', $this->translate($definition->getName() . '/editheader/0'))
				);
			}

			if ($clipboard->isNotEmpty())
			{
				$headerButtons['pasteAfter'] = sprintf(
					'<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
					BackendBindings::addToUrl('act=' . $clipboard->getMode() . '&amp;mode=2&amp;pid=' . $parentCollection->get(0)->getID()),
					specialchars($this->translate($definition->getName() . '/pasteafter/1')),
					BackendBindings::generateImage('pasteafter.gif', $this->translate($definition->getName() . '/pasteafter/0'), 'class="blink"')
				);
			}
		}

		return implode(' ', $headerButtons);
	}


	/**
	 * Show parent view mode 4.
	 *
	 * @return string HTML output
	 */
	protected function viewParent()
	{
		$definition       = $this->getEnvironment()->getDataDefinition();
		$parentCollection = $this->getEnvironment()->getCurrentParentCollection();
		$parentProvider   = $this->getEnvironment()->getDataDriver($definition->getParentDriverName());

		$objConfig        = $this->getEnvironment()->getController()->getBaseConfig();

		$this->getDC()->getEnvironment()->getPanelContainer()->initialize($objConfig);
		$strSorting       = $objConfig->getSorting();

		// Skip if we have no parent or parent collection.
		if (is_null($parentProvider) || (!$parentCollection) || ($parentCollection->length() == 0))
		{
			BackendBindings::log(
				sprintf(
					'The view for %s has either a empty parent data provider or collection.',
					$definition->getParentDriverName()
				),
				__CLASS__ . ' ' . __FUNCTION__ . '()',
				TL_ERROR
			);
			die();
			BackendBindings::redirect('contao/main.php?act=error');
		}

		// Load language file and data container array of the parent table
		BackendBindings::loadLanguageFile($definition->getParentDriverName());
		BackendBindings::loadDataContainer($definition->getParentDriverName());

		// Add template
		$objTemplate = $this->getTemplate('dcbe_general_parentView');

		// Add parent provider if exists
		if ($definition->getParentDriverName() != null)
		{
			$strPDP = $definition->getParentDriverName();
		}
		else
		{
			$strPDP = '';
		}

		$this
			->addToTemplate('tableName', strlen($definition->getName())? $definition->getName() : 'none', $objTemplate)
			->addToTemplate('collection', $this->getCurrentCollection(), $objTemplate)
			->addToTemplate('select', $this->isSelectModeActive(), $objTemplate)
			->addToTemplate('action', ampersand(\Environment::getInstance()->request, true), $objTemplate)
			->addToTemplate('header', $this->renderFormattedHeaderFields(), $objTemplate)
			->addToTemplate('hasSorting', ($strSorting == 'sorting'), $objTemplate)
			->addToTemplate('pdp', $strPDP, $objTemplate)
			->addToTemplate('cdp', $definition->getName(), $objTemplate)
			->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate)
			->addToTemplate('headerButtons', $this->getHeaderButtons(), $objTemplate);

		$this->renderEntries();

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
		$this->buildPanel();
		$this->checkClipboard();
		$this->loadCollection();

		$arrReturn            = array();
		$arrReturn['panel']   = $this->panel();
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewParent();

		return implode("\n", $arrReturn);
	}
}
