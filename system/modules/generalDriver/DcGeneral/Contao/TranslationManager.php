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

namespace DcGeneral\Contao;

use DcGeneral\TranslationManagerInterface;

class TranslationManager
	implements  TranslationManagerInterface
{
	public function getFromLanguageArray($strKey)
	{
		$chunks = explode('/', $strKey);
		$arrDca = $GLOBALS['TL_LANG'];

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $arrDca))
			{
				return null;
			}

			$arrDca = $arrDca[$chunk];
		}

		return $arrDca;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadSection($sectionName)
	{
		BackendBindings::loadLanguageFile($sectionName);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getString($path)
	{
		return $this->getFromLanguageArray($path);
	}
}
