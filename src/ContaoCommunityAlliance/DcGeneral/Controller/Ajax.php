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

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class Ajax - General purpose Ajax handler for "executePostActions" as we can not use the default Contao
 * handling.
 *
 * See Contao core issue #5957. https://github.com/contao/core/pull/5957
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 */
abstract class Ajax
{
	/**
	 * Create a new instance.
	 */
	public function __construct()
	{
		// DO NOT! call parent::__construct(); as otherwise we will end up having references in this class.
	}

	/**
	 * Compat wrapper for contao 2.X and 3.X - delegates to the relevant input handler.
	 *
	 * @param      $key
	 * @param bool $blnDecodeEntities
	 * @param bool $blnKeepUnused
	 *
	 * @return mixed
	 */
	protected static function getGet($key, $blnDecodeEntities=false, $blnKeepUnused=false)
	{
		// TODO: use dependency injection here.
		if (version_compare(VERSION, '3.0', '>='))
		{
			return \Input::get($key, $blnDecodeEntities, $blnKeepUnused);
		}
		else
		{
			return \Input::getInstance()->get($key, $blnDecodeEntities, $blnKeepUnused);
		}
	}

	/**
	 * Compat wrapper for contao 2.X and 3.X - delegates to the relevant input handler.
	 *
	 * @param      $key
	 * @param bool $blnDecodeEntities
	 *
	 * @return mixed
	 */
	protected static function getPost($key, $blnDecodeEntities=false)
	{
		// TODO: use dependency injection here.
		if (version_compare(VERSION, '3.0', '>='))
		{
			return \Input::post($key, $blnDecodeEntities);
		}
		else
		{
			return \Input::getInstance()->post($key, $blnDecodeEntities);
		}
	}

	protected static function getAjaxId()
	{
		return preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', self::getPost('id'));
	}

	protected static function getAjaxKey()
	{
		$strAjaxKey = str_replace('_' . self::getAjaxId(), '', self::getPost('id'));

		if (self::getGet('act') == 'editAll')
		{
			$strAjaxKey = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strAjaxKey);
		}

		return $strAjaxKey;
	}

	protected static function getAjaxName()
	{
		if (self::getGet('act') == 'editAll')
		{
			return preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', self::getPost('name'));
		}

		return self::getPost('name');
	}

	protected function loadStructure(DataContainerInterface $objDc)
	{
		echo $objDc->ajaxTreeView($this->getAjaxId(), intval(self::getPost('level')));
		exit;
	}

	protected function loadFileManager(DataContainerInterface $objDc)
	{
		echo $objDc->ajaxTreeView(self::getPost('folder', true), intval(self::getPost('level')));
		exit;
	}

	/**
	 * Load the page tree.
	 *
	 * @param DataContainerInterface $objDc The data container.
	 *
	 * @return mixed
	 */
	abstract protected function loadPagetree(DataContainerInterface $objDc);

	/**
	 * Load the file tree.
	 *
	 * @param DataContainerInterface $objDc The data container.
	 *
	 * @return mixed
	 */
	abstract protected function loadFiletree(DataContainerInterface $objDc);

	abstract protected function reloadPagetree(DataContainerInterface $objDc);

	abstract protected function reloadFiletree(DataContainerInterface $objDc);

	/**
	 * Handle the post actions from DcGeneral.
	 *
	 * @param DataContainerInterface $objDc The data container.
	 *
	 * @return void
	 */
	public function executePostActions(DataContainerInterface $objDc)
	{
		header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);

		$action = $objDc->getEnvironment()->getInputProvider()->getValue('action');
		switch ($action)
		{
			case 'toggleFeatured':
				// This is impossible to handle generically in DcGeneral.
			case 'toggleSubpalette':
				// DcGeneral handles sub palettes differently.
				return;

			// Load nodes of the page structure tree. Compatible between 2.X and 3.X.
			case 'loadStructure':
				$this->loadStructure($objDc);
				break;

			// Load nodes of the file manager tree.
			case 'loadFileManager':
				$this->loadFileManager($objDc);
				break;

			// Load nodes of the page tree.
			case 'loadPagetree':
				$this->loadPagetree($objDc);
				break;

				// Load nodes of the file tree.
			case 'loadFiletree':
				$this->loadFiletree($objDc);
				break;

			// Reload the page/file picker.
			case 'reloadPagetree':
				$this->reloadPagetree($objDc);
				break;
			case 'reloadFiletree':
				$this->reloadFiletree($objDc);
				break;

			// Pass unknown actions to original Contao handler.
			default:
				$ajax = new \Ajax($action);
				$ajax->executePreActions();
				$ajax->executePostActions(new DcCompat($objDc->getEnvironment()));
				break;
		}
	}
}
