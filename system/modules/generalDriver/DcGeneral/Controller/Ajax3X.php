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

namespace DcGeneral\Controller;

use DcGeneral\DataContainerInterface;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 3.X as we can not use the default
 * Contao handling.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 */
class Ajax3X extends Ajax
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function loadPagetree(DataContainerInterface $objDc)
	{
		$arrData['strTable'] = $objDc->getTable();
		$arrData['id'] = self::getAjaxName() ?: $objDc->getId();
		$arrData['name'] = self::getPost('name');

		/**
		 * @var \Contao\PageSelector $objWidget
		 */
		$objWidget = new $GLOBALS['BE_FFL']['pageSelector']($arrData, $objDc);
		echo $objWidget->generateAjax(self::getAjaxId(), self::getPost('field'), intval(self::getPost('level')));
		exit;
	}

	protected function loadFiletree(DataContainerInterface $objDc)
	{
		$arrData['strTable'] = $objDc->getTable();
		$arrData['id'] = self::getAjaxName() ?: $objDc->getId();
		$arrData['name'] = self::getPost('name');

		/**
		 * @var \Contao\FileSelector $objWidget
		 */
		$objWidget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $objDc);

		// Load a particular node
		if (self::getPost('folder', true) != '')
		{
			echo $objWidget->generateAjax(self::getPost('folder', true), self::getPost('field'), intval(self::getPost('level')));
		}
		else
		{
			echo $objWidget->generate();
		}
		exit;
	}

	protected function getTreeValue($strType)
	{
		$varValue = self::getPost('value');
		// Convert the selected values
		if ($varValue != '')
		{
			$varValue = trimsplit("\t", $varValue);

			// Automatically add resources to the DBAFS
			if ($strType == 'file')
			{
				foreach ($varValue as $k=>$v)
				{
					$varValue[$k] = \Dbafs::addResource($v)->id;
				}
			}

			$varValue = serialize($varValue);
		}

		return $varValue;
	}

	protected function reloadTree($strType, DataContainerInterface $objDc)
	{
		$intId        = self::getGet('id');
		$strFieldName = self::getPost('name');
		$strField     =preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strFieldName);

		// Handle the keys in "edit multiple" mode
		if (self::getGet('act') == 'editAll')
		{
			// TODO: change here when implementing editAll
			$intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
			$strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
		}

		if(!is_null($intId))
		{
			$objDataProvider = $objDc->getDataProvider();
			$objModel        = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($intId));

			if (is_null($objModel))
			{
				$this->log('A record with the ID "' . $intId . '" does not exist in "' . $objDc->getTable() . '"', 'Ajax executePostActions()', TL_ERROR);
				header('HTTP/1.1 400 Bad Request');
				die('Bad Request');
			}
		}

		$varValue = $this->getTreeValue($strType);
		$strKey = $strType . 'Tree';

		// Set the new value
		if (!is_null($objModel))
		{
			$objModel->setProperty($strField, $varValue);
			$arrAttribs['activeRecord'] = $objModel;
		}
		else
		{
			$arrAttribs['activeRecord'] = null;
		}

		$arrAttribs['id'] = $strFieldName;
		$arrAttribs['name'] = $strFieldName;
		$arrAttribs['value'] = $varValue;
		$arrAttribs['strTable'] = $objDc->getTable();
		$arrAttribs['strField'] = $strField;

		/**
		 * @var \Widget $objWidget
		 */
		$objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
		echo $objWidget->generate();

		exit;
	}

	protected function reloadPagetree(DataContainerInterface $objDc)
	{
		$this->reloadTree('page', $objDc);
	}

	protected function reloadFiletree(DataContainerInterface $objDc)
	{
		$this->reloadTree('file', $objDc);
	}
}
