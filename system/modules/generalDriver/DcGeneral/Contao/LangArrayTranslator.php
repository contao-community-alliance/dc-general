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

use DcGeneral\AbstractTranslator;

class LangArrayTranslator extends AbstractTranslator
{
	protected function getValue($string, $domain, $locale)
	{
		if (!$domain) {
			$domain = 'default';
		}

		BackendBindings::loadLanguageFile($domain, $locale);

		if (!is_array($GLOBALS['TL_LANG'][$domain])) {
			return $string;
		}

		$chunks = explode('.', $string);
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
