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

/**
 * Contains multiple palettes, organised by its name.
 */
interface PaletteCollectionInterface
{
	/**
	 * Return all palettes in this collection.
	 *
	 * @return array|PaletteInterface[]
	 */
	public function getPalettes();

	/**
	 * Check if a palette for the given name exists in this collection.
	 *
	 * @param string $paletteName
	 *
	 * @return bool
	 */
	public function hasPalette($paletteName);

	/**
	 * Return the palette for the given name.
	 *
	 * @param string $paletteName
	 *
	 * @return PaletteInterface
	 *
	 * @throws \InvalidArgumentException Is thrown if there is no palette with this name.
	 */
	public function getPalette($paletteName);
}
