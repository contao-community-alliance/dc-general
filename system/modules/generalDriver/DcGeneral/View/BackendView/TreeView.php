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

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use DcGeneral\View\BackendView\Event\GetPasteRootButtonEvent;

class TreeView extends BaseView
{
	/**
	 * Load the collection of child items and the parent item for the currently selected parent item.
	 *
	 * @return ListView
	 *
	 * @throws \RuntimeException
	 */
	public function loadCollection($rootId = null, $intLevel = 0)
	{
		$environment     = $this->getEnvironment();
		$definition      = $environment->getDataDefinition();
		$dataDriver      = $environment->getDataDriver();
		$inputProvider   = $environment->getInputProvider();

		// TODO: @CS we need this to be srctable_dsttable_tree for interoperability, for mode5 this will be self_self_tree but with strTable.
		$strToggleID = $definition->getName() . '_tree';

		$arrOpenParents = $inputProvider->getPersistentValue($strToggleID);
		if (!is_array($arrOpenParents))
		{
			$arrOpenParents = array();
		}

		// Check if the open/close all is active
		if ($inputProvider->getParameter('ptg') == 'all')
		{
			$arrOpenParents = array();
			if (!array_key_exists('all', $arrOpenParents))
			{
				$arrOpenParents = array();
				$arrOpenParents['all'] = 1;
			}

			// Save in session and redirect
			$inputProvider->setPersistentValue($strToggleID, $arrOpenParents);
			$this->redirectHome();
		}
/*
		$arrNeededFields = $this->calcNeededFields($dataDriver->getEmptyModel(), $definition->getName());
		$arrTitlePattern = $this->calcLabelPattern($definition->getName());
*/
		$objCollection = $environment->getController()->getTreeCollectionRecursive($rootId, $arrOpenParents, $intLevel);

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
			$environment->setCurrentCollection($objTableTreeData);
		}
		else
		{
			$environment->setCurrentCollection($objCollection);
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

		$objTemplate = $this->getTemplate('dcbe_general_treeview_entry');

		$this
			->addToTemplate('objModel', $objModel, $objTemplate)
			->addToTemplate('intMode', $this->getDataDefinition()->getSortingMode(), $objTemplate)
			->addToTemplate('strToggleID', $strToggleID, $objTemplate);

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
	 * @return string
	 */
	protected function viewTree()
	{
		$definition = $this->getDataDefinition();

		// Init some Vars
		switch ($definition->getSortingMode())
		{
			case 6:
				$treeClass = 'tree_xtnd';
				break;
			// case 5:
			default:
				$treeClass = 'tree';
		}

		// Label + Icon
		if (strlen($definition->getLabel()) == 0 )
		{
			$strLabelText = 'DC General Tree BackendView Ultimate';
		}
		else
		{
			$strLabelText = $definition->getLabel();
		}
		if (strlen($definition->getIcon()) == 0 )
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
			$buttonEvent  = new GetPasteRootButtonEvent();
			$buttonEvent
				->setEnvironment($this->getEnvironment())
				->setCircularReference(false)
				->setPrevious(null)
				->setNext(null)
				->setHref(
					BackendBindings::addToUrl(sprintf('act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;childs=%s',
						$objClipboard->getMode(),
						$objClipboard->getContainedIds(),
						$objClipboard->getCircularIds()
					))
				)
				->setPasteDisabled(false);

			$this->dispatchEvent(GetPasteRootButtonEvent::NAME, $buttonEvent);

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
		$objTemplate->tableName    = $this->getEnvironment()->getDataDriver()->getEmptyModel()->getProviderName();
		$objTemplate->strLabelIcon = BackendBindings::generateImage($strLabelIcon);
		$objTemplate->strLabelText = $strLabelText;
		$objTemplate->strHTML      = $this->generateTreeView($this->getCurrentCollection(), $treeClass);
		$objTemplate->intMode      = $this->getDataDefinition()->getSortingMode();
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
		$this->checkClipboard();
		$this->loadCollection();

		$arrReturn            = array();
		if ($this->getDataDefinition()->getSortingMode() == 5)
		{
			$arrReturn['panel'] = $this->panel();
		}
		$arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
		$arrReturn['body']    = $this->viewTree();

		// Return all
		return implode("\n", $arrReturn);
	}

	public function ajaxTreeView($intID, $intLevel)
	{
		$this->checkClipboard();
		$this->loadCollection($intID, $intLevel);

		$treeClass = '';
		switch ($this->getDataDefinition()->getSortingMode())
		{
			case 5:
				$treeClass = 'tree';
				break;

			case 6:
				$treeClass = 'tree_xtnd';
				break;
		}

		$strHTML = $this->generateTreeView($this->getCurrentCollection(), $this->getDataDefinition()->getSortingMode(), $treeClass);

		return $strHTML;
	}
}
