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
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;

class TreeView extends BaseView
{
	protected function getToggleId()
	{
		return $this->getEnvironment()->getDataDefinition()->getName() . '_tree';
	}

	protected function getOpenElements()
	{
		$inputProvider = $this->getEnvironment()->getInputProvider();

		$openElements = $inputProvider->getPersistentValue($this->getToggleId());

		if (!is_array($openElements))
		{
			$openElements = array();
			$inputProvider->setPersistentValue($this->getToggleId(), $openElements);
		}

		// Check if the open/close all is active
		if ($inputProvider->getParameter('ptg') == 'all')
		{
			$openElements = array();
			if (!array_key_exists('all', $openElements))
			{
				$openElements = array();
				$openElements['all'] = 1;
			}

			// Save in session and reload.
			$inputProvider->setPersistentValue($this->getToggleId(), $openElements);
			BackendBindings::reload();
		}

		return $openElements;
	}

	protected function toggleModel($providerName, $id)
	{
		$inputProvider = $this->getEnvironment()->getInputProvider();
		$openElements  = $this->getOpenElements();

		if (!isset($openElements[$providerName]))
		{
			$openElements[$providerName] = array();
		}

		if (!isset($openElements[$providerName][$id]))
		{
			$openElements[$providerName][$id] = 1;
		}
		else
		{
			$openElements[$providerName][$id] = !$openElements[$providerName][$id];
		}

		$inputProvider->setPersistentValue($this->getToggleId(), $openElements);
	}

	/**
	 * @param ModelInterface $model
	 *
	 * @return bool
	 */
	protected function isModelOpen($model)
	{
		$openModels  = $this->getOpenElements();

		if (isset($openModels['all']) && ($openModels['all'] == 1))
		{
			return true;
		}

		if (isset($openModels[$model->getProviderName()][$model->getID()]) && ($openModels[$model->getProviderName()][$model->getID()]))
		{
			return true;
		}

		return false;
	}


	/**
	 * Load the collection of child items and the parent item for the currently selected parent item.
	 *
	 * @param mixed $rootId       The root element (or null to fetch everything).
	 *
	 * @param int   $intLevel     The current level in the tree (of the optional root element).
	 *
	 * @param null  $providerName The data provider from which the optional root element shall be taken from.
	 *
	 * @return CollectionInterface
	 */
	public function loadCollection($rootId = null, $intLevel = 0, $providerName = null)
	{
		$environment     = $this->getEnvironment();
		$dataDriver      = $environment->getDataProvider($providerName);

		$objCollection = $this->getTreeCollectionRecursive($rootId, $intLevel, $providerName);

		if ($rootId)
		{
			$objTableTreeData = $dataDriver->getEmptyCollection();
			$objModel = $objCollection->get(0);
			foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
			{
				foreach ($objCollection as $objSubModel)
				{
					$objTableTreeData->add($objSubModel);
				}
			}
			return $objTableTreeData;
		}
		else
		{
			return $objCollection;
		}
	}

	protected function calcLabelFields($strTable)
	{
		return $config = $this->getViewSection()->getListingConfig()->getLabelFormatter($strTable)->getPropertyNames();
	}

	/**
	 * @param ModelInterface $model
	 *
	 * @param                $level
	 *
	 */
	protected function determineModelState(ModelInterface $model, $level)
	{
		$model->setMeta(DCGE::TREE_VIEW_LEVEL, $level);
		$model->setMeta(DCGE::TREE_VIEW_IS_OPEN, $this->isModelOpen($model));
	}

	/**
	 * This "renders" a model for tree view.
	 *
	 * @param ModelInterface $objModel     the model to render.
	 *
	 * @param int   $intLevel     the current level in the tree hierarchy.
	 *
	 * @param array $arrSubTables the tables that shall be rendered "below" this item.
	 *
	 */
	protected function treeWalkModel(ModelInterface $objModel, $intLevel, $arrSubTables = array())
	{
		$relationships = $this->getEnvironment()->getDataDefinition()->getModelRelationshipDefinition();
		$blnHasChild = false;

		$this->determineModelState($objModel, $intLevel);

		$arrChildCollections = array();
		foreach ($arrSubTables as $strSubTable)
		{
			// evaluate the child filter for this item.
			$arrChildFilter = $relationships->getChildCondition($objModel->getProviderName(), $strSubTable);

			// if we do not know how to render this table within here, continue with the next one.
			if (!$arrChildFilter)
			{
				continue;
			}

			// Create a new Config
			$objChildConfig = $this->getEnvironment()->getDataProvider($strSubTable)->getEmptyConfig();
			$objChildConfig->setFilter($arrChildFilter->getFilter($objModel));

			$objChildConfig->setFields($this->calcLabelFields($strSubTable));

			// TODO: hardcoded sorting... NOT GOOD!
			$objChildConfig->setSorting(array('sorting' => 'ASC'));

			// Fetch all children
			$objChildCollection = $this->getEnvironment()->getDataProvider($strSubTable)->fetchAll($objChildConfig);

			$blnHasChild = ($objChildCollection->length() > 0);

			// Speed up
			if ($blnHasChild && !$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
			{
				break;
			}
			elseif ($blnHasChild)
			{
				foreach ($objChildCollection as $objChildModel)
				{
					// let the child know about it's parent.
					$objModel->setMeta(DCGE::MODEL_PID, $objModel->getID());
					$objModel->setMeta(DCGE::MODEL_PTABLE, $objModel->getProviderName());

					$mySubTables = array();
					foreach ($relationships->getChildConditions($objModel->getProviderName()) as $condition)
					{
						$mySubTables[] = $condition->getDestinationName();
					}

					$this->treeWalkModel($objChildModel, $intLevel + 1, $mySubTables);
				}
				$arrChildCollections[] = $objChildCollection;

				// speed up, if not open, one item is enough to break as we have some children.
				if (!$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
				{
					break;
				}
			}
		}

		// If open store children
		if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) && count($arrChildCollections) != 0)
		{
			$objModel->setMeta(DCGE::TREE_VIEW_CHILD_COLLECTION, $arrChildCollections);
		}

		$objModel->setMeta(DCGE::TREE_VIEW_HAS_CHILDS, $blnHasChild);
	}

	/**
	 * Recursively retrieve a collection of all complete node hierarchy.
	 *
	 * @param array $rootId         The ids of the root node.
	 *
	 * @param int   $intLevel       The level the items are residing on.
	 *
	 * @param null  $providerName   The data provider from which the root element originates from.
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getTreeCollectionRecursive($rootId, $intLevel = 0, $providerName = null)
	{
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$dataDriver       = $environment->getDataProvider($providerName);
		$objTableTreeData = $dataDriver->getEmptyCollection();
		$objRootConfig    = $environment->getController()->getBaseConfig();
		$relationships    = $definition->getModelRelationshipDefinition();

		$this->getPanel()->initialize($objRootConfig);
		$objRootConfig->setFields($this->calcLabelFields($definition->getBasicDefinition()->getDataProvider()));

		if (!$rootId)
		{
			$objRootCondition = $definition->getModelRelationshipDefinition()->getRootCondition();

			if ($objRootCondition)
			{
				$arrBaseFilter = $objRootConfig->getFilter();
				$arrFilter     = $objRootCondition->getFilterArray();

				if ($arrBaseFilter)
				{
					$arrFilter = array_merge($arrBaseFilter, $arrFilter);
				}

				$objRootConfig->setFilter(array(array(
					'operation' => 'AND',
					'children'    => $arrFilter,
				)));
			}
			// Fetch all root elements
			$objRootCollection = $dataDriver->fetchAll($objRootConfig);

			if ($objRootCollection->length() > 0)
			{
				$mySubTables = array();
				foreach ($relationships->getChildConditions($objRootCollection->get(0)->getProviderName()) as $condition)
				{
					$mySubTables[] = $condition->getDestinationName();
				}

				foreach ($objRootCollection as $objRootModel)
				{
					/** @var ModelInterface $objRootModel */
					$objTableTreeData->add($objRootModel);
					$this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
				}
			}

			return $objTableTreeData;
		}
		else
		{
			$objRootConfig->setId($rootId);
			// Fetch root element
			$objRootModel = $dataDriver->fetch($objRootConfig);

			$mySubTables = array();
			foreach ($relationships->getChildConditions($objRootModel->getProviderName()) as $condition)
			{
				$mySubTables[] = $condition->getDestinationName();
			}

			$this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
			$objRootCollection = $dataDriver->getEmptyCollection();
			$objRootCollection->add($objRootModel);

			return $objRootCollection;
		}
	}

	/**
	 * @param ModelInterface $objModel
	 *
	 * @param string $strToggleID
	 *
	 * @return string
	 */
	protected function parseModel($objModel, $strToggleID)
	{
		$objModel->setMeta(DCGE::MODEL_BUTTONS, $this->generateButtons($objModel, $objModel->getProviderName()));
		$objModel->setMeta(DCGE::MODEL_LABEL_VALUE, $this->formatModel($objModel));

		$objTemplate = $this->getTemplate('dcbe_general_treeview_entry');

		if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
		{
			$toggleTitle = $this->getEnvironment()->getTranslator()->translate('collapseNode', 'MSC');
		}
		else
		{
			$toggleTitle = $this->getEnvironment()->getTranslator()->translate('expandNode', 'MSC');
		}

		$toggleScript = sprintf(
			'Backend.getScrollOffset(); return BackendGeneral.loadSubTree(this, {\'toggler\':\'%s\', \'id\':\'%s\', \'providerName\':\'%s\', \'level\':\'%s\', \'mode\':\'%s\'});',
			$strToggleID,
			$objModel->getId(),
			$objModel->getProviderName(),
			$objModel->getMeta('dc_gen_tv_level'),
			// FIXME: add real tree mode here.
			6// intMode
		);

		$this
			->addToTemplate('environment', $this->getEnvironment(), $objTemplate)
			->addToTemplate('objModel', $objModel, $objTemplate)
			// FIXME: add real tree mode here.
			->addToTemplate('intMode', 6, $objTemplate)
			->addToTemplate('strToggleID', $strToggleID, $objTemplate)
			->addToTemplate('toggleUrl', BackendBindings::addToUrl('ptg=' . $objModel->getId() . '&amp;provider=' . $objModel->getProviderName()), $objTemplate)
			->addToTemplate('toggleTitle', $toggleTitle, $objTemplate)
			->addToTemplate('toggleScript', $toggleScript, $objTemplate)
		;

		return $objTemplate->parse();
	}

	/**
	 * @param \DcGeneral\Data\CollectionInterface $objCollection The collection to iterate over.
	 *
	 * @param string                              $treeClass     The class to use for the tree.
	 *
	 * @return string
	 */
	protected function generateTreeView($objCollection, $treeClass)
	{
		$arrHTML = array();

		foreach ($objCollection as $objModel)
		{
			/** @var \DcGeneral\Data\ModelInterface $objModel */

			$strToggleID = $objModel->getProviderName() . '_' . $treeClass . '_' . $objModel->getID();

			$arrHTML[] = $this->parseModel($objModel, $strToggleID);

			if ($objModel->getMeta(DCGE::TREE_VIEW_HAS_CHILDS) && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
			{
				$objTemplate = $this->getTemplate('dcbe_general_treeview_child');
				$strSubHTML  = '';

				foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
				{
					$strSubHTML .= $this->generateTreeView($objCollection, $treeClass);
				}

				$this
					->addToTemplate('objParentModel', $objModel, $objTemplate)
					->addToTemplate('strToggleID', $strToggleID, $objTemplate)
					->addToTemplate('strHTML', $strSubHTML, $objTemplate)
					->addToTemplate('strTable', $objModel->getProviderName(), $objTemplate);

				$arrHTML[] = $objTemplate->parse();
			}
		}

		return implode("\n", $arrHTML);
	}

	/**
	 * Render the paste button for pasting into the root of the tree.
	 *
	 * @param GetPasteRootButtonEvent $event
	 *
	 * @return string
	 */
	public static function renderPasteRootButton(GetPasteRootButtonEvent $event)
	{
		if (!is_null($event->getHtml()))
		{
			return $event->getHtml();
		}

		$strLabel = $GLOBALS['TL_LANG'][$event->getEnvironment()->getDataDefinition()->getName()]['pasteinto'][0];
		if ($event->isPasteDisabled())
		{
			return BackendBindings::generateImage('pasteinto_.gif', $strLabel, 'class="blink"');
		}

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$event->getHref(),
			specialchars($strLabel),
			'onclick="Backend.getScrollOffset()"',
			BackendBindings::generateImage('pasteinto.gif', $strLabel, 'class="blink"')
		);
	}

	/**
	 * Render the tree view.
	 *
	 * @param CollectionInterface $collection
	 *
	 * @return string
	 */
	protected function viewTree($collection)
	{
		$definition = $this->getDataDefinition();

		// Init some Vars
		switch (6 /*$definition->getSortingMode()*/)
		{
			case 6:
				$treeClass = 'tree_xtnd';
				break;
			// case 5:
			default:
				$treeClass = 'tree';
		}

		// Label + Icon
		// FIXME: we need the tree root element label here.
		if (true || strlen($this->getViewSection()->getListingConfig()->getLabel()) == 0)
		{
			$strLabelText = 'DC General Tree BackendView Ultimate';
		}
		else
		{
			$strLabelText = $definition->getLabel();
		}

		// FIXME: we need the tree root element icon here.
		if (true || strlen($definition->getIcon()) == 0 )
		{
			$strLabelIcon = 'pagemounts.gif';
		}
		else
		{
			$strLabelIcon = $definition->getIcon();
		}

		// Root paste into
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$objClipboard = $this->getEnvironment()->getClipboard();
			$buttonEvent  = new GetPasteRootButtonEvent($this->getEnvironment());
			$buttonEvent
				->setHref(
					BackendBindings::addToUrl(sprintf('act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;childs=%s',
						$objClipboard->getMode(),
						$objClipboard->getContainedIds(),
						$objClipboard->getCircularIds()
					))
				)
				->setPasteDisabled(false);

			$this->getEnvironment()->getEventPropagator()->propagate(
				$buttonEvent,
				$this->getEnvironment()->getDataDefinition()->getName()
			);

			$strRootPasteInto = $this->renderPasteRootButton($buttonEvent);
		}
		else
		{
			$strRootPasteInto = '';
		}

		// Build template
		// FIXME: dependency injection or rather template factory?
		$objTemplate               = new \BackendTemplate('dcbe_general_treeview');
		$objTemplate->treeClass    = 'tl_' . $treeClass;
		$objTemplate->tableName    = $definition->getName();
		$objTemplate->strLabelIcon = BackendBindings::generateImage($strLabelIcon);
		$objTemplate->strLabelText = $strLabelText;
		$objTemplate->strHTML      = $this->generateTreeView($collection, $treeClass);
		// FIXME: set real tree mode here.
		$objTemplate->intMode      = 6;
		$objTemplate->strRootPasteinto = $strRootPasteInto;

		// Add breadcrumb, if we have one
		$strBreadcrumb = $this->breadcrumb();
		if($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
		}

		// Return :P
		return $objTemplate->parse();
	}

	/**
	 * Show all entries from one table
	 *
	 * @return string HTML
	 */
	public function showAll()
	{
		$input = $this->getEnvironment()->getInputProvider();
		if (($id = $input->hasParameter('ptg')) && ($providerName = $input->hasParameter('provider')))
		{
			$this->toggleModel($providerName, $id);
			$this->redirectHome();
		}

		$this->checkClipboard();

		$collection = $this->loadCollection();

		$arrReturn            = array();
/*
		if ($this->getDataDefinition()->getSortingMode() == 5)
		{
			$arrReturn['panel'] = $this->panel();
		}
*/
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewTree($collection);

		// Return all
		return implode("\n", $arrReturn);
	}

	public function handleAjaxCall()
	{
		$input = $this->getEnvironment()->getInputProvider();

		switch ($input->getValue('action'))
		{
			case 'DcGeneralLoadSubTree':
				header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);
				echo $this->ajaxTreeView(
					$input->getValue('id'),
					$input->getValue('providerName'),
					$input->getValue('level'),
					$input->getValue('mode')
				);
				exit;
		}

		parent::handleAjaxCall();
	}

	public function ajaxTreeView($id, $providerName, $level, $treeMode)
	{
		$this->toggleModel($providerName, $id);

		$collection = $this->loadCollection($id, $level, $providerName);

		$treeClass = '';
		switch (6 /*$definition->getSortingMode()*/)
		{
			case 5:
				$treeClass = 'tree';
				break;

			case 6:
				$treeClass = 'tree_xtnd';
				break;
		}

		$strHTML = $this->generateTreeView($collection, $treeClass);

		return $strHTML;
	}
}
