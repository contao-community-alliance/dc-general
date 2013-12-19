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

/**
 * Class LangArrayTranslator.
 *
 * Contao language array translator implementation.
 *
 * @package DcGeneral\Contao
 */
class LangArrayTranslator extends AbstractTranslator
{
	/**
	 * {@inheritDoc}
	 */
	protected function getValue($string, $domain, $locale)
	{
		if (!$domain)
		{
			$domain = 'default';
		}

		BackendBindings::loadLanguageFile($domain, $locale);

		// We have to treat 'languages', 'default', 'modules' etc. domains differently.
		if (!(is_array($GLOBALS['TL_LANG'][$domain])) && (substr($domain, 0, 2) != 'tl_'))
		{
			$lang = $GLOBALS['TL_LANG'];
		}
		else
		{
			if (!is_array($GLOBALS['TL_LANG'][$domain]))
			{
				return $string;
			}
			$lang = $GLOBALS['TL_LANG'][$domain];
		}

		$chunks = explode('.', $string);

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
