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
use DcGeneral\View\DefaultView\Events\GetGroupHeaderEvent;
use DcGeneral\View\DefaultView\Events\GetParentHeaderEvent;
use DcGeneral\View\DefaultView\Events\ModelToLabelEvent;

class ParentView extends BaseView
{
	/**
	 * Render the entries for parent view.
	 */
	protected function renderEntries()
	{
		$definition   = $this->getEnvironment()->getDataDefinition();
		$firstSorting = $this->getDC()->getFirstSorting();

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
			if (!$this->getDataDefinition()->isGroupingDisabled() && $firstSorting != 'sorting')
			{
				// get a list with all fields for sorting
				$orderBy = $this->getDataDefinition()->getAdditionalSorting();

				// Default ASC
				if (count($orderBy) == 0)
				{
					$sortingMode = 9;
				}
				// If the current First sorting is the default one use the global flag
				else if ($firstSorting == $orderBy[0])
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
				}
				// Use the field flag, if given
				else if ($this->getDC()->arrDCA['fields'][$firstSorting]['flag'] != '')
				{
					$sortingMode = $this->getDC()->arrDCA['fields'][$firstSorting]['flag'];
				}
				// Use the global as fallback
				else
				{
					$sortingMode = $this->getDC()->arrDCA['list']['sorting']['flag'];
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
			if (!$this->getDC()->isSelectSubmit())
			{
				$strPrevious = ((!is_null($this->getCurrentCollection()->get($i - 1))) ? $this->getCurrentCollection()->get($i - 1)->getID() : null);
				$strNext = ((!is_null($this->getCurrentCollection()->get($i + 1))) ? $this->getCurrentCollection()->get($i + 1)->getID() : null);

				$buttons = $this->generateButtons($objModel, $this->getDC()->getTable(), $this->getDC()->getEnvironment()->getRootIds(), false, null, $strPrevious, $strNext);

				$objModel->setMeta(DCGE::MODEL_BUTTONS, $buttons);
			}

			$objModel->setMeta(DCGE::MODEL_LABEL_VALUE, $this->getDC()->getCallbackClass()->childRecordCallback($objModel));
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
		$add = array();
		// TODO: need a mapping for this.
		$headerFields = $this->getDC()->arrDCA['list']['sorting']['headerFields'];

		$parentDefinition = $this->getEnvironment()->getParentDataDefinition();
		$parentName       = $this->getEnvironment()->getDataDefinition()->getParentDriverName();

		foreach ($headerFields as $v)
		{
			$_v = deserialize($this->getEnvironment()->getCurrentParentCollection()->get(0)->getProperty($v));

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
					$_v = strlen($_v) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
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
				$key = isset($GLOBALS['TL_LANG'][$parentName][$v][0]) ? $GLOBALS['TL_LANG'][$parentName][$v][0] : $v;
				$add[$key] = $_v;
			}
		}

		$event = new GetParentHeaderEvent();
		$event
			->setEnvironment($this->getEnvironment())
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
	 * Show parent view mode 4.
	 *
	 * @return string HTML output
	 */
	protected function viewParent()
	{
		$definition       = $this->getEnvironment()->getDataDefinition();
		$parentdefinition = $this->getEnvironment()->getParentDataDefinition();

		// Skip if we have no parent or parent collection.
		if (is_null($this->getEnvironment()->getDataDriver($definition->getParentDriverName())) || $this->getEnvironment()->getCurrentParentCollection()->length() == 0)
		{
			$this->log('The view for ' . $definition->getParentDriverName() . 'has either a empty parent dataprovider or collection.', __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Load language file and data container array of the parent table
		BackendBindings::loadLanguageFile($definition->getParentDriverName());
		BackendBindings::loadDataContainer($definition->getParentDriverName());

		// Get parent DC Driver
		// TODO: who ever did this, you can't be serious - REFACTOR!
		$objParentDC = new \DC_General($definition->getParentDriverName());
		$this->parentDca = $objParentDC->getDCA();

		// Add template
		// FIXME: dependency injection or rather template factory?
		$objTemplate = new \BackendTemplate('dcbe_general_parentView');

		$objTemplate->tableName  = strlen($definition->getName())? $definition->getName() : 'none';
		$objTemplate->collection = $this->getCurrentCollection();
		$objTemplate->select     = $this->getDC()->isSelectSubmit();
		$objTemplate->action     = ampersand(\Environment::getInstance()->request, true);
		$objTemplate->mode       = $definition->getSortingMode();
		$objTemplate->header     = $this->renderFormattedHeaderFields();
		$objTemplate->hasSorting = ($this->getDC()->getFirstSorting() == 'sorting');

		// Get dataprovider from current and parent
		$strCDP = $this->getEnvironment()->getDataDriver()->getEmptyModel()->getProviderName();
		$strPDP = $this->getEnvironment()->getDataDriver($this->getEnvironment()->getDataDefinition()->getParentDriverName());

		// Add parent provider if exists
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

		$this->renderEntries();

		$objTemplate->editHeader = array(
			'content' => BackendBindings::generateImage('edit.gif', $GLOBALS['TL_LANG'][$definition->getName()]['editheader'][0]),
			'href' => preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->getEnvironment()->getDataDefinition()->getParentDriverName()) ? '&amp;table=' . $this->getEnvironment()->getDataDefinition()->getParentDriverName() : ''), BackendBindings::addToUrl('act=edit')),
			'title' => specialchars($GLOBALS['TL_LANG'][$definition->getName()]['editheader'][1])
		);

		$objTemplate->pasteNew = array(
			'content' => BackendBindings::generateImage('new.gif', $GLOBALS['TL_LANG'][$definition->getName()]['pasteafter'][0]),
			'href' => BackendBindings::addToUrl('act=create&amp;mode=2&amp;pid=' . $this->getEnvironment()->getCurrentParentCollection()->get(0)->getID() . '&amp;id=' . $this->intId),
			'title' => specialchars($GLOBALS['TL_LANG'][$definition->getName()]['pastenew'][0])
		);

		$objTemplate->pasteAfter = array(
			'content' => BackendBindings::generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$definition->getName()]['pasteafter'][0], 'class="blink"'),
			'href' => BackendBindings::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $this->getEnvironment()->getCurrentParentCollection()->get(0)->getID() . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')),
			'title' => specialchars($GLOBALS['TL_LANG'][$definition->getName()]['pasteafter'][0])
		);

		$objTemplate->notDeletable      = !$definition->isDeletable();
		$objTemplate->notEditable       = !$definition->isEditable();
		$objTemplate->notEditableParent = !$parentdefinition->isEditable();

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
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
		// Create return value
		$arrReturn            = array();
		$arrReturn['panel']   = $this->panel();
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewParent();

		// Return all
		return implode("\n", $arrReturn);
	}
}
