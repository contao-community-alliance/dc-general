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

use DcGeneral\TranslatorInterface;

class LangArrayTranslator implements  TranslatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function translate($string, $domain = null)
	{
		if (!$domain) {
			$domain = 'default';
		}

		BackendBindings::loadLanguageFile($domain);

		if (!is_array($GLOBALS['TL_LANG'][$domain])) {
			return $string;
		}

		$chunks = explode('/', $string);
		$lang = $GLOBALS['TL_LANG'][$domain];

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $lang))
			{
				return $string;
			}

			$lang = $lang[$chunk];
		}

		return $lang;
	}
}
