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

namespace DcGeneral\DataDefinition;

interface ListLabelInterface
{
	/**
	 * One or more fields that will be shown in the list.
	 *
	 * @return string[]
	 */
	public function getFields();

	/**
	 * HTML string used to format the fields that will be shown (e.g. %s).
	 */
	public function getFormat();

	/**
	 * Maximum number of characters of the label.
	 *
	 * @return int
	 */
	public function getMaxCharacters();

	/**
	 * Table listing like in Contao members module.
	 *
	 * @return bool
	 */
	public function isShowColumnsActive();
}
