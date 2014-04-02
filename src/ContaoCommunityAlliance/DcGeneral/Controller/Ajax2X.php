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

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 2.X as we can not use the default
 * Contao handling.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 */
class Ajax2X extends Ajax
{
	protected function loadPagetree(DataContainerInterface $objDc)
	{
		$arrData['strTable'] = $objDc->getEnvironment()->getDataDefinition()->getName();
		$arrData['id'] = self::getAjaxName() ?: $objDc->getId();
		$arrData['name'] = self::getPost('name');

		/**
		 * @var \PageSelector $objWidget
		 */
		$objWidget = new $GLOBALS['BE_FFL']['pageTree']($arrData, $objDc);
		echo $objWidget->generateAjax(self::getAjaxId(), self::getPost('field'), intval(self::getPost('level')));
		exit;
	}

	protected function loadFiletree(DataContainerInterface $objDc)
	{
		$arrData['strTable'] = $objDc->getEnvironment()->getDataDefinition()->getName();
		$arrData['id'] = self::getAjaxName() ?: $objDc->getId();
		$arrData['name'] = self::getPost('name');

		/**
		 * @var \FileTree $objWidget
		 */
		$objWidget = new $GLOBALS['BE_FFL']['fileTree']($arrData, $objDc);

		// Load a particular node
		if (self::getPost('folder', true) != '')
		{
			echo $objWidget->generateAjax(self::getPost('folder', true), self::getPost('field'), intval(self::getPost('level')));
		}
		else
		{
			// Reload the whole tree.
			$this->import('BackendUser', 'User');
			$strTree = '';

			// Set a custom path
			if (strlen($GLOBALS['TL_DCA'][$objDc->getEnvironment()->getDataDefinition()->getName()]['fields'][self::getPost('field')]['eval']['path']))
			{
				$strTree = $objWidget->generateAjax(
					$GLOBALS['TL_DCA'][$objDc->getEnvironment()->getDataDefinition()->getName()]['fields'][self::getPost('field')]['eval']['path'],
					self::getPost('field'),
					intval(self::getPost('level'))
				);
			}
			// Start from root
			elseif ($this->User->isAdmin)
			{
				$strTree = $objWidget->generateAjax(
					$GLOBALS['TL_CONFIG']['uploadPath'],
					self::getPost('field'),
					intval(self::getPost('level'))
				);
			}
			// Set filemounts
			else
			{
				foreach ($this->eliminateNestedPaths($this->User->filemounts) as $node)
				{
					$strTree .= $objWidget->generateAjax(
						$node,
						self::getPost('field'),
						intval(self::getPost('level')),
						true
					);
				}
			}

			echo $strTree;
		}
		exit;
	}

	protected function reloadPagetree(DataContainerInterface $objDc)
	{
		throw new DcGeneralRuntimeException('Contao 3.X only.');
	}

	protected function reloadFiletree(DataContainerInterface $objDc)
	{
		throw new DcGeneralRuntimeException('Contao 3.X only.');
	}
}
