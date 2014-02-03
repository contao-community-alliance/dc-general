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
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\Contao\BackendBindings;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class TreeView.
 *
 * Implementation for tree displaying.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class TreeView extends BaseView
{
	/**
	 * Retrieve the id for this view.
	 *
	 * @return string
	 */
	protected function getToggleId()
	{
		return $this->getEnvironment()->getDataDefinition()->getName() . '_tree';
	}

	/**
	 * Retrieve the ids of all tree nodes that are expanded.
	 *
	 * @return array
	 */
	protected function getOpenElements()
	{
		$inputProvider = $this->getEnvironment()->getInputProvider();

		$openElements = $inputProvider->getPersistentValue($this->getToggleId());

		if (!is_array($openElements))
		{
			$openElements = array();
			$inputProvider->setPersistentValue($this->getToggleId(), $openElements);
		}

		// Check if the open/close all is active.
		if ($inputProvider->getParameter('ptg') == 'all')
		{
			$openElements = array();
			if (!array_key_exists('all', $openElements))
			{
				$openElements        = array();
				$openElements['all'] = 1;
			}

			// Save in session and reload.
			$inputProvider->setPersistentValue($this->getToggleId(), $openElements);

			$this->getEnvironment()->getEventPropagator()->propagate(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
		}

		return $openElements;
	}

	/**
	 * Toggle the model with the given id from the given provider.
	 *
	 * @param string $providerName The data provider name.
	 *
	 * @param mixed  $id           The id of the model.
	 *
	 * @return void
	 */
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
	 * Determine if the passed model is expanded.
	 *
	 * @param ModelInterface $model The model to check.
	 *
	 * @return bool
	 */
	protected function isModelOpen($model)
	{
		$openModels = $this->getOpenElements();

		if (isset($openModels['all']) && ($openModels['all'] == 1))
		{
			return true;
		}

		if (isset($openModels[$model->getProviderName()][$model->getID()])
			&& ($openModels[$model->getProviderName()][$model->getID()])
		)
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
		$environment = $this->getEnvironment();
		$dataDriver  = $environment->getDataProvider($providerName);

		$objCollection = $this->getTreeCollectionRecursive($rootId, $intLevel, $providerName);

		if ($rootId)
		{
			$objTreeData = $dataDriver->getEmptyCollection();
			$objModel    = $objCollection->get(0);
			foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
			{
				foreach ($objCollection as $objSubModel)
				{
					$objTreeData->push($objSubModel);
				}
			}
			return $objTreeData;
		}

		return $objCollection;
	}

	/**
	 * Load the parent model for the current list.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 *
	 * @throws DcGeneralRuntimeException If the parent view requirements are not fulfilled - either no data provider
	 *                                   defined or no parent model id given.
	 */
	protected function loadParentModel()
	{
		$environment = $this->getEnvironment();

		if (!($parentId = $environment->getInputProvider()->getParameter('pid')))
		{
			throw new DcGeneralRuntimeException(
				'TreeView needs a proper parent id defined, somehow none is defined?',
				1
			);
		}

		if (!($objParentProvider =
			$environment->getDataProvider(
				$environment->getDataDefinition()->getBasicDefinition()->getParentDataProvider()
			)
		))
		{
			throw new DcGeneralRuntimeException(
				'TreeView needs a proper parent data provider defined, somehow none is defined?',
				1
			);
		}

		$objParentItem = $objParentProvider->fetch($objParentProvider->getEmptyConfig()->setId($parentId));

		if (!$objParentItem)
		{
			// No parent item found, might have been deleted.
			// We transparently create it for our filter to be able to filter to nothing.
			// TODO: shall we rather bail with "parent not found"?
			$objParentItem = $objParentProvider->getEmptyModel();
			$objParentItem->setID($parentId);
		}

		return $objParentItem;
	}

	/**
	 * Calculate the fields needed by a tree label for the given data provider name.
	 *
	 * @param string $strTable The name of the data provider.
	 *
	 * @return array
	 */
	protected function calcLabelFields($strTable)
	{
		return $config = $this->getViewSection()->getListingConfig()->getLabelFormatter($strTable)->getPropertyNames();
	}

	/**
	 * Check the state of a model and set the metadata accordingly.
	 *
	 * @param ModelInterface $model The model of which the state shall be checked of.
	 *
	 * @param int            $level The tree level the model is contained within.
	 *
	 * @return void
	 */
	protected function determineModelState(ModelInterface $model, $level)
	{
		$model->setMeta(DCGE::TREE_VIEW_LEVEL, $level);
		$model->setMeta(DCGE::TREE_VIEW_IS_OPEN, $this->isModelOpen($model));
	}

	/**
	 * This "renders" a model for tree view.
	 *
	 * @param ModelInterface $objModel     The model to render.
	 *
	 * @param int            $intLevel     The current level in the tree hierarchy.
	 *
	 * @param array          $arrSubTables The names of data providers that shall be rendered "below" this item.
	 *
	 * @return void
	 */
	protected function treeWalkModel(ModelInterface $objModel, $intLevel, $arrSubTables = array())
	{
		$relationships = $this->getEnvironment()->getDataDefinition()->getModelRelationshipDefinition();
		$blnHasChild   = false;

		$this->determineModelState($objModel, $intLevel);

		$arrChildCollections = array();
		foreach ($arrSubTables as $strSubTable)
		{
			// Evaluate the child filter for this item.
			$arrChildFilter = $relationships->getChildCondition($objModel->getProviderName(), $strSubTable);

			// If we do not know how to render this table within here, continue with the next one.
			if (!$arrChildFilter)
			{
				continue;
			}

			// Create a new Config and fetch the children from the child provider.
			$objChildConfig = $this->getEnvironment()->getDataProvider($strSubTable)->getEmptyConfig();
			$objChildConfig->setFilter($arrChildFilter->getFilter($objModel));

			$objChildConfig->setFields($this->calcLabelFields($strSubTable));

			// TODO: hardcoded sorting... NOT GOOD!
			$objChildConfig->setSorting(array('sorting' => 'ASC'));

			$objChildCollection = $this->getEnvironment()->getDataProvider($strSubTable)->fetchAll($objChildConfig);

			$blnHasChild = ($objChildCollection->length() > 0);

			// Speed up - we may exit if we have at least one child but the parenting model is collapsed.
			if ($blnHasChild && !$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
			{
				break;
			}
			elseif ($blnHasChild)
			{
				foreach ($objChildCollection as $objChildModel)
				{
					// Let the child know about it's parent.
					$objModel->setMeta(DCGE::MODEL_PID, $objModel->getID());
					$objModel->setMeta(DCGE::MODEL_PTABLE, $objModel->getProviderName());

					$mySubTables = array();
					foreach ($relationships->getChildConditions($objModel->getProviderName()) as $condition)
					{
						$mySubTables[] = $condition->getDestinationName();
					}

					$this->treeWalkModel($objChildModel, ($intLevel + 1), $mySubTables);
				}
				$arrChildCollections[] = $objChildCollection;

				// Speed up, if collapsed, one item is enough to break as we have some children.
				if (!$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
				{
					break;
				}
			}
		}

		// If expanded, store children.
		if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) && count($arrChildCollections) != 0)
		{
			$objModel->setMeta(DCGE::TREE_VIEW_CHILD_COLLECTION, $arrChildCollections);
		}

		$objModel->setMeta(DCGE::TREE_VIEW_HAS_CHILDS, $blnHasChild);
	}

	/**
	 * Recursively retrieve a collection of all complete node hierarchy.
	 *
	 * @param array  $rootId       The ids of the root node.
	 *
	 * @param int    $intLevel     The level the items are residing on.
	 *
	 * @param string $providerName The data provider from which the root element originates from.
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
			// Fetch all root elements.
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
					$objTableTreeData->push($objRootModel);
					$this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
				}
			}

			return $objTableTreeData;
		}

		$objRootConfig->setId($rootId);
		// Fetch root element.
		$objRootModel = $dataDriver->fetch($objRootConfig);

		$mySubTables = array();
		foreach ($relationships->getChildConditions($objRootModel->getProviderName()) as $condition)
		{
			$mySubTables[] = $condition->getDestinationName();
		}

		$this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
		$objRootCollection = $dataDriver->getEmptyCollection();
		$objRootCollection->push($objRootModel);

		return $objRootCollection;
	}

	/**
	 * Render a given model.
	 *
	 * @param ModelInterface $objModel    The model to render.
	 *
	 * @param string         $strToggleID The id of the toggler.
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
			// FIXME: add real tree mode here - intMode.
			6
		);

		$toggleUrlEvent = new AddToUrlEvent('ptg=' . $objModel->getId() . '&amp;provider=' . $objModel->getProviderName());
		$this->getEnvironment()->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

		$this
			->addToTemplate('environment', $this->getEnvironment(), $objTemplate)
			->addToTemplate('objModel', $objModel, $objTemplate)
			// FIXME: add real tree mode here.
			->addToTemplate('intMode', 6, $objTemplate)
			->addToTemplate('strToggleID', $strToggleID, $objTemplate)
			->addToTemplate(
				'toggleUrl',
				$toggleUrlEvent->getUrl(),
				$objTemplate
			)
			->addToTemplate('toggleTitle', $toggleTitle, $objTemplate)
			->addToTemplate('toggleScript', $toggleScript, $objTemplate);

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
		$arrHtml = array();

		foreach ($objCollection as $objModel)
		{
			/** @var \DcGeneral\Data\ModelInterface $objModel */

			$strToggleID = $objModel->getProviderName() . '_' . $treeClass . '_' . $objModel->getID();

			$arrHtml[] = $this->parseModel($objModel, $strToggleID);

			if ($objModel->getMeta(DCGE::TREE_VIEW_HAS_CHILDS) && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
			{
				$objTemplate = $this->getTemplate('dcbe_general_treeview_child');
				$strSubHtml  = '';

				foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
				{
					$strSubHtml .= $this->generateTreeView($objCollection, $treeClass);
				}

				$this
					->addToTemplate('objParentModel', $objModel, $objTemplate)
					->addToTemplate('strToggleID', $strToggleID, $objTemplate)
					->addToTemplate('strHTML', $strSubHtml, $objTemplate)
					->addToTemplate('strTable', $objModel->getProviderName(), $objTemplate);

				$arrHtml[] = $objTemplate->parse();
			}
		}

		return implode("\n", $arrHtml);
	}

	/**
	 * Render the paste button for pasting into the root of the tree.
	 *
	 * @param GetPasteRootButtonEvent $event The event that has been triggered.
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
			/** @var GenerateHtmlEvent $imageEvent */
			$imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
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
		$imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent(
				'pasteinto.gif',
				$strLabel,
				'class="blink"'
			)
		);

		return sprintf(' <a href="%s" title="%s" %s>%s</a>',
			$event->getHref(),
			specialchars($strLabel),
			'onclick="Backend.getScrollOffset()"',
			$imageEvent->getHtml()
		);
	}

	/**
	 * Render the tree view.
	 *
	 * @param CollectionInterface $collection The collection of items.
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
		if (true || strlen($definition->getIcon()) == 0)
		{
			$strLabelIcon = 'pagemounts.gif';
		}
		else
		{
			$strLabelIcon = $definition->getIcon();
		}

		// Root paste into.
		if ($this->getEnvironment()->getClipboard()->isNotEmpty())
		{
			$objClipboard = $this->getEnvironment()->getClipboard();
			/** @var AddToUrlEvent $urlEvent */
			$urlEvent = $this->getEnvironment()->getEventPropagator()->propagate(
				ContaoEvents::BACKEND_ADD_TO_URL,
				new AddToUrlEvent(sprintf('act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;childs=%s',
					$objClipboard->getMode(),
					$objClipboard->getContainedIds(),
					$objClipboard->getCircularIds()
				))
			);

			$buttonEvent = new GetPasteRootButtonEvent($this->getEnvironment());
			$buttonEvent
				->setHref($urlEvent->getUrl())
				->setPasteDisabled(false);

			$this->getEnvironment()->getEventPropagator()->propagate(
				$buttonEvent::NAME,
				$buttonEvent,
				$this->getEnvironment()->getDataDefinition()->getName()
			);

			$strRootPasteInto = $this->renderPasteRootButton($buttonEvent);
		}
		else
		{
			$strRootPasteInto = '';
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $this->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent($strLabelIcon)
		);

		// Build template.
		// FIXME: dependency injection or rather template factory?
		$objTemplate                   = new \BackendTemplate('dcbe_general_treeview');
		$objTemplate->treeClass        = 'tl_' . $treeClass;
		$objTemplate->tableName        = $definition->getName();
		$objTemplate->strLabelIcon     = $imageEvent->getHtml();
		$objTemplate->strLabelText     = $strLabelText;
		$objTemplate->strHTML          = $this->generateTreeView($collection, $treeClass);
		$objTemplate->strRootPasteinto = $strRootPasteInto;
		// FIXME: set real tree mode here.
		$objTemplate->intMode = 6;

		// Add breadcrumb, if we have one.
		$strBreadcrumb = $this->breadcrumb();
		if ($strBreadcrumb != null)
		{
			$objTemplate->breadcrumb = $strBreadcrumb;
		}

		return $objTemplate->parse();
	}

	public function enforceModelRelationship($model)
	{
		$definition = $this->getEnvironment()->getDataDefinition();
		$basic      = $definition->getBasicDefinition();
		// No op in this base class but implemented in subclasses to enforce parent<->child relationship.
		$parent = $this->loadParentModel();

		$condition = $definition
			->getModelRelationshipDefinition()
			->getChildCondition(
				$basic->getParentDataProvider(),
				$basic->getDataProvider()
			);

		if ($condition)
		{
			$condition->applyTo($parent, $model);
		}
	}

	/**
	 * Show all entries from one table.
	 *
	 * @return string
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
		$arrReturn  = array();

/*
		if ($this->getDataDefinition()->getSortingMode() == 5)
		{
			$arrReturn['panel'] = $this->panel();
		}
*/
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewTree($collection);

		return implode("\n", $arrReturn);
	}

	/**
	 * Handle an ajax call.
	 *
	 * @return void
	 */
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

			default:
		}

		parent::handleAjaxCall();
	}

	/**
	 * Handle ajax rendering of a sub tree.
	 *
	 * @param string $id           Id of the root node.
	 *
	 * @param string $providerName Name of the data provider where the model is contained within.
	 *
	 * @param int    $level        Level depth of the model in the whole tree.
	 *
	 * @return string
	 */
	public function ajaxTreeView($id, $providerName, $level)
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

			default:
		}

		$strHtml = $this->generateTreeView($collection, $treeClass);

		return $strHtml;
	}
}
