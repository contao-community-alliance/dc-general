<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Controller;

use DcGeneral\DataContainerInterface;

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
abstract class Ajax extends \Backend
{
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

	abstract protected function loadPagetree(DataContainerInterface $objDc);

	abstract protected function loadFiletree(DataContainerInterface $objDc);

	abstract protected function reloadPagetree(DataContainerInterface $objDc);

	abstract protected function reloadFiletree(DataContainerInterface $objDc);

	protected function toggleFeatured(DataContainerInterface $objDc)
	{
		// TODO: this solution is really a mess, we DEFINATELY want to implement a proper functionality in the callback class to handle this.
		$strClass = $objDc->getTable();
		if (class_exists($strClass, false))
		{
			$dca = new $strClass();

			if (method_exists($dca, 'toggleFeatured'))
			{
				$dca->toggleFeatured(self::getPost('id'), ((self::getPost('state') == 1) ? true : false));
			}
		}
		exit;
	}

	protected function toggleSubpalette(DataContainerInterface $objDc)
	{
		/**
		 * @var \Contao\BackendUser $objUser
		 */
		$objUser = \BackendUser::getInstance();
		$arrDCA  = $objDc->getDCA();
		$field   = self::getPost('field');

		// Check whether the field is a selector field and allowed for regular users (contao/core/#4427).
		if (!is_array($arrDCA['palettes']['__selector__'])
			|| !in_array($field, $arrDCA['palettes']['__selector__'])
			|| ($arrDCA['fields'][$field]['exclude'] && !$objUser->hasAccess($objDc->getTable() . '::' . $field, 'alexf'))
		)
		{
			$this->log(
				'Field "' . $field . '" is not an allowed selector field (possible SQL injection attempt)',
				'Ajax executePostActions()',
				TL_ERROR
			);
			header('HTTP/1.1 400 Bad Request');
			die('Bad Request');
		}

		if (self::getPost('load'))
		{
			if (self::getGet('act') == 'editAll')
			{
				throw new \RuntimeException("Ajax editAll unimplemented, I do not know what to do.", 1);
				echo $objDc->editAll(self::getAjaxId(), self::getPost('id'));
			}
			else{
				echo $objDc->generateAjaxPalette(self::getPost('field'));
			}
		}
		exit;
	}

	protected function callHooks($strAction, DataContainerInterface $objDc)
	{
		if (isset($GLOBALS['TL_HOOKS']['executePostActions']) && is_array($GLOBALS['TL_HOOKS']['executePostActions']))
		{
			foreach ($GLOBALS['TL_HOOKS']['executePostActions'] as $callback)
			{
				if (in_array('getInstance', get_class_methods($callback[0])))
				{
					$objHook = call_user_func(array($callback[0], 'getInstance'));
				}
				else
				{
					$objHook = new $callback[0]();
				}

				$objHook->$callback[1]($strAction, $objDc);
			}
		}
		exit;
	}

	/**
	 *
	 * @param String $strAction
	 * @param DataContainerInterface $objDc
	 * @return void
	 */
	public function executePostActions(DataContainerInterface $objDc)
	{
		// Check DC for a right driver
		if (!$objDc instanceof DataContainerInterface)
		{
			return;
		}
		header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);

		switch (self::getPost('action'))
		{
			// Load nodes of the page structure tree. Compatible between 2.X and 3.X
			case 'loadStructure':
				$this->loadStructure($objDc);
				break;

			// Load nodes of the file manager tree
			case 'loadFileManager':
				$this->loadFileManager($objDc);
				break;

			// Load nodes of the page tree
			case 'loadPagetree':
				$this->loadPagetree($objDc);
				break;

				// Load nodes of the file tree
			case 'loadFiletree':
				$this->loadFiletree($objDc);
				break;

			// Reload the page/file picker
			case 'reloadPagetree':
				$this->reloadPagetree($objDc);
				break;
			case 'reloadFiletree':
				$this->reloadFiletree($objDc);
				break;

			// Feature/unfeature an element
			case 'toggleFeatured':
				$this->toggleFeatured($objDc);
				break;

			// Toggle subpalettes
			case 'toggleSubpalette':
				$this->toggleSubpalette($objDc);
				break;

			// HOOK: pass unknown actions to callback functions
			default:
				$this->callHooks(self::getPost('action'), $objDc);
				 break;
		}
	}
}
