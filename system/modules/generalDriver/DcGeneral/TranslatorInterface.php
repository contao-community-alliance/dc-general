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

namespace DcGeneral;

interface TranslatorInterface
{
	/**
	 * Translate a string in a specific domain.
	 *
	 * @param string $string     The translation string.
	 * @param string $domain     The translation domain.
	 * @param array  $parameters Parameters used in translation.
	 * @param string $locale     The translation locale.
	 *
	 * @return string
	 */
	public function translate($string, $domain = null, array $parameters = array(), $locale = null);
}
