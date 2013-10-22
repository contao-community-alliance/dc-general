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
use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\View\DefaultView\Events\ModelToLabelEvent;

class ListView extends BaseView
{
	/**
	 * Load the collection of child items and the parent item for the currently selected parent item.
	 *
	 * @return ListView
	 *
	 * @throws DcGeneralRuntimeException
	 */
	public function loadCollection()
	{
		$environment            = $this->getEnvironment();
		$definition             = $environment->getDataDefinition();
		$objCurrentDataProvider = $environment->getDataDriver();
		$objParentDataProvider  = $environment->getDataDriver($definition->getParentDriverName());
		$objConfig              = $environment->getController()->getBaseConfig();

		$environment->getPanelContainer()->initialize($objConfig);

		$objCollection = $objCurrentDataProvider->fetchAll($objConfig);

		$environment->setCurrentCollection($objCollection);

		// If we want to group the elements, do so now.
		if (isset($objCondition) && ($definition->getSortingMode() == 3))
		{
			foreach ($objCollection as $objModel)
			{
				/** @var ModelInterface $objModel */
				$arrFilter = $objCondition->getInverseFilter($objModel);
				$objConfig = $objParentDataProvider->getEmptyConfig()->setFilter($arrFilter);
				$objParent = $objParentDataProvider->fetch($objConfig);

				// TODO: wouldn't it be wiser to link the model instance instead of the id of the parenting model?
				$objModel->setMeta(DCGE::MODEL_PID, $objParent->getId());
			}
		}
	}

	protected function getTableHead()
	{
		$arrTableHead = array();
		$definition   = $this->getEnvironment()->getDataDefinition();

		// Generate the table header if the "show columns" option is active.
		if ($definition->getListLabel()->isShowColumnsActive())
		{
			foreach ($definition->getPropertyNames() as $f)
			{
				$property = $definition->getProperty($f);
				if ($property)
				{
					$label = $property->getLabel();
				}
				else
				{
					$label = $f;
				}

				$arrTableHead[] = array(
					'class' => 'tl_folder_tlist col_' . $f . ((in_array($f, $definition->getAdditionalSorting())) ? ' ordered_by' : ''),
					'content' => $label[0]
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
	 * Get arguments for label
	 *
	 * @param \DcGeneral\Data\ModelInterface $objModelRow
	 * @return array
	 */
	protected function getListViewLabelArguments($objModelRow)
	{
		$args = array();

		// Label
		foreach ($this->getEnvironment()->getDataDefinition()->getListLabel()->getFields() as $k => $v)
		{
			$property = $this->getDataDefinition()->getProperty($v);

			// skip unknown properties.
			if (!$property)
			{
				continue;
			}

			$value = $objModelRow->getProperty($v);
			$eval  = $property->getEvaluation();

			// TODO: IMO all of this should be independent or at least be pushed into some kind of data scraper.
			if (strpos($v, ':') !== false)
			{
				$args[$k] = $objModelRow->getMeta(DCGE::MODEL_LABEL_ARGS);
			}
			elseif (in_array($property->get('flag'), array(5, 6, 7, 8, 9, 10)))
			{

				switch ($eval['rgxp'])
				{
					case 'date':
						$format = $GLOBALS['TL_CONFIG']['dateFormat'];
					break;
					case 'time':
						$format = $GLOBALS['TL_CONFIG']['timeFormat'];
					break;
					default:
						$format = $GLOBALS['TL_CONFIG']['datimFormat'];
					break;
				}

				$args[$k] = BackendBindings::parseDate($format, $value);
			}
			elseif ($property->getWidgetType() == 'checkbox' && !$eval['multiple'])
			{
				if (strlen($value))
				{
					$label = $property->getLabel();
					$args[$k] = $label[0] ? $label[0] : $v;
				}
				else{
					$args[$k] = '';
				}
			}
			else
			{
				$row       = deserialize($value);
				$reference = $property->get('reference');
				$options   = $property->get('options');

				if (is_array($row))
				{
					$args_k = array();
					foreach ($row as $option)
					{
						if (strlen($reference[$option]))
						{
							$args_k[] = $reference[$option];
						}
						else
						{
							$args_k[] = $option;
						}
					}

					$args[$k] = implode(', ', $args_k);
				}
				elseif (isset($reference[$value]))
				{
					$args[$k] = is_array($reference[$value]) ? $reference[$value][0] : $reference[$value];
				}
				elseif (($eval['isAssociative'] || array_is_assoc($property->get('options'))) && array_key_exists($value, $options))
				{
					$args[$k] = $options[$value];
				}
				else
				{
					$args[$k] = $value;
				}
			}
		}

		return $args;
	}

	/**
	 * Set label for list view
	 */
	protected function setListViewLabel()
	{
		$definition   = $this->getEnvironment()->getDataDefinition();
		$listLabel    = $definition->getListLabel();
		$firstSorting = $definition->getFirstSorting();

		// FIXME: this is not possible with the new environmental approach.
		// Automatically add the "order by" field as last column if we do not have group headers
		if ($listLabel->isShowColumnsActive() && !in_array($firstSorting, $listLabel->getFields()))
		{
			$this->getDC()->arrDCA['list']['label']['fields'][] = $firstSorting;
		}

		$remoteCur = false;
		$groupclass = 'tl_folder_tlist';
		$eoCount = -1;

		foreach ($this->getCurrentCollection() as $objModelRow)
		{
			/** @var \DcGeneral\Data\ModelInterface $objModelRow */
			$args = $this->getListViewLabelArguments($objModelRow);

			// Shorten the label if it is too long
			$labelFormat = (strlen($listLabel->getFormat()) ? $listLabel->getFormat() : '%s');
			if (count($args) == substr_count($labelFormat, '%') - substr_count($labelFormat, '%%'))
			{
				$label = vsprintf($labelFormat, $args);
			}
			else
			{
				$label = '';
			}

			if ($listLabel->getMaxCharacters() > 0 && $listLabel->getMaxCharacters() < strlen(strip_tags($label)))
			{
				$label = trim(BackendBindings::substrHtml($label, $listLabel->getMaxCharacters())) . ' â€¦';
			}

			// Remove empty brackets (), [], {}, <> and empty tags from the label
			$label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
			$label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

			// Build the sorting groups
			if ($definition->getSortingMode() > 0)
			{
				// Get the current value of first sorting
				if ($firstSorting)
				{
					$property = $definition->getProperty($firstSorting);
					$current  = $objModelRow->getProperty($firstSorting);
					$orderBy  = $definition->getAdditionalSorting();

					// Default ASC
					if (count($orderBy) == 0)
					{
						$sortingMode = 9;
					}
					// Use the fild flag, if given
					else if ($property->get('flag') != '')
					{
						$sortingMode = $property->get('flag');
					}
					// ToDo: Should we remove this, because we allready have the fallback ?
					// If the current First sorting is the default one use the global flag
					else if ($firstSorting == $orderBy[0])
					{
						$sortingMode = $definition->getSortingFlag();
					}
					// Use the global as fallback
					else
					{
						$sortingMode = $definition->getSortingFlag();
					}

					// ToDo: Why such a big if ?
//				$sortingMode = (count($orderBy) == 1 && $firstSorting == $orderBy[0] && $this->getDC()->arrDCA['list']['sorting']['flag'] != '' && $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'] == '') ? $this->getDC()->arrDCA['list']['sorting']['flag'] : $this->getDC()->arrDCA['fields'][$this->getDC()->getFirstSorting()]['flag'];

					$remoteNew = $this->formatCurrentValue($firstSorting, $current, $sortingMode);

					// Add the group header
					if (!$listLabel->isShowColumnsActive() && !$this->getDataDefinition()->isGroupingDisabled() && ($remoteNew != $remoteCur || $remoteCur === false))
					{
						$eoCount = -1;

						$objModelRow->setMeta(DCGE::MODEL_GROUP_VALUE, array(
							'class' => $groupclass,
							'value' => $this->formatGroupHeader($firstSorting, $remoteNew, $sortingMode, $objModelRow)
						));

						$groupclass = 'tl_folder_list';
						$remoteCur = $remoteNew;
					}
				}
			}

			$objModelRow->setMeta(DCGE::MODEL_EVEN_ODD_CLASS, ((++$eoCount % 2 == 0) ? 'even' : 'odd'));

			$colspan = 1;

			$event = new ModelToLabelEvent();
			$event
				->setEnvironment($this->getEnvironment())
				->setModel($objModelRow)
				->setLabel($label)
				->setListLabel($listLabel)
				->setArgs($args);

			$this->dispatchEvent(ModelToLabelEvent::NAME, $event);

			if (!is_null($event->getArgs()))
			{
				$newArgs = $event->getArgs();
				// Handle strings and arrays (backwards compatibility)
				if (!$listLabel->isShowColumnsActive())
				{
					$label = vsprintf((strlen($listLabel->getFormat()) ? $listLabel->getFormat() : '%s'), (array) $newArgs);
				}
				elseif (!is_array($newArgs))
				{
					$colspan = count($listLabel->getFields());
				}
			}

			$arrLabel = array();

			// Add columns
			if ($listLabel->isShowColumnsActive())
			{
				$fields = $listLabel->getFields();
				foreach ($args as $j => $arg)
				{
					$arrLabel[] = array(
						'colspan' => $colspan,
						'class' => 'tl_file_list col_' . $fields[$j] . (($fields[$j] == $firstSorting) ? ' ordered_by' : ''),
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
	 * Generate list view from current collection
	 *
	 * @return string
	 */
	protected function viewList()
	{
		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();

		// Set label
		$this->setListViewLabel();

		// Generate buttons
		$collection = $this->getCurrentCollection();
		foreach ($collection as $i => $objModel)
		{
			// Regular buttons - only if not in select mode!
			if (!$this->isSelectModeActive())
			{
				$strPrevious = ((!is_null($collection->get($i - 1))) ? $collection->get($i - 1)->getID() : null);
				$strNext     = ((!is_null($collection->get($i + 1))) ? $collection->get($i + 1)->getID() : null);
				/**
				 * @var \DcGeneral\Data\ModelInterface $objModel
				 */
				$objModel->setMeta(
					DCGE::MODEL_BUTTONS,
					$this->generateButtons(
						$objModel,
						$definition->getName(),
						$environment->getRootIds(),
						false,
						null,
						$strPrevious,
						$strNext
					)
				);
			}
		}

		// FIXME: hack, we should define a better handling for manual sorting.
		$sorting = $definition->getFirstSorting();

		$panel = $this->getEnvironment()->getPanelContainer()->getPanel('sorting');
		if ($panel)
		{
			/** @var \DcGeneral\Panel\SortElementInterface $panel */
			$sorting = $panel->getSelected();
		}

		// Add template
		if ($sorting == 'sorting')
		{
			// FIXME: dependency injection or rather template factory?
			$objTemplate = new \BackendTemplate('dcbe_general_listView_sorting');
		}
		else
		{
			// FIXME: dependency injection or rather template factory?
			$objTemplate = new \BackendTemplate('dcbe_general_listView');
		}

		$this
			->addToTemplate('tableName', strlen($definition->getName())? $definition->getName() : 'none', $objTemplate)
			->addToTemplate('collection', $this->getCurrentCollection(), $objTemplate)
			->addToTemplate('select', $this->getEnvironment()->getInputProvider()->getParameter('act'), $objTemplate)
			->addToTemplate('action', ampersand(\Environment::getInstance()->request, true), $objTemplate)
			->addToTemplate('mode', $definition->getSortingMode(), $objTemplate)
			->addToTemplate('tableHead', $this->getTableHead(), $objTemplate)
			// Set dataprovider from current and parent
			->addToTemplate('pdp', '', $objTemplate)
			->addToTemplate('cdp', $definition->getName(), $objTemplate)
			->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate);

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$this->addToTemplate('breadcrumb', $strBreadcrumb, $objTemplate);
		}

		return $objTemplate->parse();
	}


	/**
	 * @return String
	 */
	public function copy()
	{
		return $this->edit();
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
		$arrReturn['body']    = $this->viewList();

		// Return all
		return implode("\n", $arrReturn);
	}
}
