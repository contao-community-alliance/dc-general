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
use DcGeneral\View\DefaultView\Events\GetPasteRootButtonEvent;

class TreeView extends BaseView
{
	/**
	 * @param \DcGeneral\Data\CollectionInterface $objCollection
	 *
	 * @param string                              $treeClass
	 *
	 * @return string
	 */
	protected function generateTreeView($objCollection, $treeClass)
	{
		$arrHTML = array();

		$strDriverName = $this->getEnvironment()->getDataDefinition()->getName();

		foreach ($objCollection as $objModel)
		{
			/** @var \DcGeneral\Data\ModelInterface $objModel */
			$objModel->setMeta(DCGE::MODEL_BUTTONS, $this->generateButtons($objModel, $strDriverName));

			$strToggleID = $strDriverName . '_' . $treeClass . '_' . $objModel->getID();

			// FIXME: dependency injection or rather template factory?
			$objEntryTemplate              = new \BackendTemplate('dcbe_general_treeview_entry');
			$objEntryTemplate->objModel    = $objModel;
			$objEntryTemplate->intMode     = $this->getDataDefinition()->getSortingMode();
			$objEntryTemplate->strToggleID = $strToggleID;

			$arrHTML[] = $objEntryTemplate->parse();

			if ($objModel->getMeta(DCGE::TREE_VIEW_HAS_CHILDS) == true && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) == true)
			{
				// FIXME: dependency injection or rather template factory?
				$objChildTemplate                 = new \BackendTemplate('dcbe_general_treeview_child');
				$objChildTemplate->objParentModel = $objModel;
				$objChildTemplate->strToggleID    = $strToggleID;
				$strSubHTML                       = '';

				foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
				{
					$strSubHTML .= $this->generateTreeView($objCollection, $treeClass);
				}

				$objChildTemplate->strHTML  = $strSubHTML;
				$objChildTemplate->strTable = $strDriverName;

				$arrHTML[] = $objChildTemplate->parse();
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
			$strLabelText = 'DC General Tree DefaultView Ultimate';
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
		// Create return value
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
}
