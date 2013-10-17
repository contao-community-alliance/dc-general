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

namespace DcGeneral\DataDefinition\Palette;

use DcGeneral\DataDefinition\PropertyInterface;

/**
 * A palette contains a set of properties, grouped by legends.
 */
interface PaletteInterface
{
	/**
	 * Return the name of this palette.
	 *
	 * @return array|PaletteInterface[]
	 */
	public function getName();

	/**
	 * Get all properties in this palette.
	 *
	 * @return PropertyInterface[]
	 */
	public function getProperties();

	/**
	 * Return the palette for the given name.
	 *
	 * @param string $paletteName
	 *
	 * @return PaletteInterface
	 *
	 * @throws
	 */
	public function getLegends();
}
