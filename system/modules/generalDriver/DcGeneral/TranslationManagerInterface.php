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

interface TranslationManagerInterface
{
	/**
	 * Load a translation section into memory.
	 *
	 * Note: All previous loaded sections have to be persistent.
	 *
	 * @param string $sectionName
	 *
	 * @return TranslationManagerInterface
	 */
	public function loadSection($sectionName);

	/**
	 * Retrieve a string from the translation buffer.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getString($path);
}
