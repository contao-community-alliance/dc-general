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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;

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
		$environment = $objDc->getEnvironment();
		$input       = $environment->getInputProvider();
		$folder      = $input->getValue('folder');
		$field       = $input->getParameter('field');
		$level       = intval($input->getValue('level'));

		$arrData['strTable'] = $objDc->getEnvironment()->getDataDefinition()->getName();
		// $arrData['id']       = self::getAjaxName() ?: $objDc->getId();
		$arrData['name']     = $field;

		/** @var \PageSelector $objWidget */
		$objWidget        = new $GLOBALS['BE_FFL']['pageSelector']($arrData, $objDc);
		$objWidget->value = $this->getTreeValue($arrData['name']);

		echo $objWidget->generateAjax($folder, $field, $level);
		exit;
	}

	protected function loadFiletree(DataContainerInterface $objDc)
	{
		$environment = $objDc->getEnvironment();
		$input       = $environment->getInputProvider();
		$folder      = $input->getValue('folder');
		$field       = $input->getParameter('field');
		$level       = intval($input->getValue('level'));

		$arrData['strTable'] = $input->getParameter('table');
		$arrData['id']       = $field;
		$arrData['name']     = $field;

		/** @var \FileSelector $objWidget */
		$objWidget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $objDc);

		$objWidget->value = $this->getTreeValue($field);
		// Load a particular node.
		if ($folder != '')
		{
			echo $objWidget->generateAjax($folder, $field, $level);
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
					if(version_compare(VERSION, '3.2', '<'))
					{
						$varValue[$k] = \Dbafs::addResource($v)->id;
					}
					else
					{
						$varValue[$k] = \Dbafs::addResource($v)->uuid;
					}
				}
			}

			$varValue = serialize($varValue);
		}

		return $varValue;
	}

	/**
	 * Reload the file tree.
	 *
	 * @param string                 $strType The type.
	 *
	 * @param DataContainerInterface $objDc   The data container.
	 */
	protected function reloadTree($strType, DataContainerInterface $objDc)
	{
		$environment  = $objDc->getEnvironment();
		$input        = $environment->getInputProvider();
		$serializedId = $input->hasParameter('id') ? $input->getParameter('id') : null;
		$fieldName    = $input->hasValue('name') ? $input->getValue('name') : null;

		// Handle the keys in "edit multiple" mode.
		if (self::getGet('act') == 'editAll')
		{
			// TODO: change here when implementing editAll
			$serializedId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $fieldName);
			$field        = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $fieldName);
		}

		if (!is_null($serializedId))
		{
			$id = IdSerializer::fromSerialized($serializedId);

			$dataProvider = $objDc->getEnvironment()->getDataProvider($id->getDataProviderName());
			$model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($id->getId()));

			if (is_null($model))
			{
				$this->log(
					'A record with the ID "' . $serializedId . '" does not exist in "' .
					$objDc->getEnvironment()->getDataDefinition()->getName() . '"',
					'Ajax executePostActions()',
					TL_ERROR
				);
				header('HTTP/1.1 400 Bad Request');
				die('Bad Request');
			}
		}

		$varValue = $this->getTreeValue($strType);
		$strKey   = $strType . 'Tree';

		// Set the new value.
		if (isset($model))
		{
			$model->setProperty($fieldName, $varValue);
			$arrAttribs['activeRecord'] = $model;
		}
		else
		{
			$arrAttribs['activeRecord'] = null;
		}

		$arrAttribs['id']       = $fieldName;
		$arrAttribs['name']     = $fieldName;
		$arrAttribs['value']    = $varValue;
		$arrAttribs['strTable'] = $objDc->getEnvironment()->getDataDefinition()->getName();
		$arrAttribs['strField'] = $fieldName;

		/** @var \Widget $objWidget */
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
