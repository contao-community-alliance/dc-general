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

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Contains multiple palettes, organised by its name.
 */
interface PaletteCollectionInterface
{
	/**
	 * Remove all palettes from this collection.
	 *
	 * @return PaletteCollectionInterface
	 */
	public function clearPalettes();

	/**
	 * Set all palettes in this collection.
	 *
	 * @param array|PaletteInterface[] $palettes
	 *
	 * @return PaletteCollectionInterface
	 */
	public function setPalettes(array $palettes);

	/**
	 * Add multiple palettes to this collection.
	 *
	 * @param PaletteInterface[] $palettes
	 *
	 * @return PaletteInterface
	 */
	public function addPalettes(array $palettes);

	/**
	 * Add a palette to this collection.
	 *
	 * @param PaletteInterface $palette
	 *
	 * @return PaletteInterface
	 */
	public function addPalette(PaletteInterface $palette);

	/**
	 * Remove a palette from this collection.
	 *
	 * @param PaletteInterface $palette
	 *
	 * @return PaletteInterface
	 */
	public function removePalette(PaletteInterface $palette);

	/**
	 * Return all palettes in this collection.
	 *
	 * @return array|PaletteInterface[]
	 */
	public function getPalettes();

	/**
	 * Check if a palette exists in this collection.
	 *
	 * @param string $paletteName
	 *
	 * @return bool
	 */
	public function hasPalette(PaletteInterface $palette);

	/**
	 * Find the palette matching model and input parameters.
	 *
	 * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
	 * @param PropertyValueBag $input If given, selectors will be evaluated depending on the input data.
	 *
	 * @return PaletteInterface
	 *
	 * @throws DcGeneralRuntimeException Is thrown if there is no palette found.
	 */
	public function findPalette(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Check if a palette for the given name exists in this collection.
	 *
	 * @deprecated Only for backwards compatibility, we will remove palette names in the future!
	 *
	 * @param string $paletteName
	 *
	 * @return bool
	 */
	public function hasPaletteByName($paletteName);

	/**
	 * Return the palette for the given name.
	 *
	 * @deprecated Only for backwards compatibility, we will remove palette names in the future!
	 *
	 * @param string $paletteName
	 *
	 * @return PaletteInterface
	 *
	 * @throws DcGeneralInvalidArgumentException Is thrown if there is no palette with this name.
	 */
	public function getPaletteByName($paletteName);

	/**
	 * Create a deep clone of the palette collection.
	 */
	public function __clone();
}
