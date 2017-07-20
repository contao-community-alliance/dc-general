<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

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
     * @param array|PaletteInterface[] $palettes The palettes.
     *
     * @return PaletteCollectionInterface
     */
    public function setPalettes(array $palettes);

    /**
     * Add multiple palettes to this collection.
     *
     * @param array|PaletteInterface[] $palettes The palettes.
     *
     * @return PaletteInterface
     */
    public function addPalettes(array $palettes);

    /**
     * Add a palette to this collection.
     *
     * @param PaletteInterface $palette The palette.
     *
     * @return PaletteInterface
     */
    public function addPalette(PaletteInterface $palette);

    /**
     * Remove a palette from this collection.
     *
     * @param PaletteInterface $palette The palette.
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
     * @param PaletteInterface $palette The palette.
     *
     * @return bool
     */
    public function hasPalette(PaletteInterface $palette);

    /**
     * Find the palette matching model and input parameters.
     *
     * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
     *
     * @param PropertyValueBag    $input If given, selectors will be evaluated depending on the input data.
     *
     * @return PaletteInterface
     */
    public function findPalette(ModelInterface $model = null, PropertyValueBag $input = null);

    /**
     * Check if a palette for the given name exists in this collection.
     *
     * @param string $paletteName The palette name.
     *
     * @return bool
     */
    public function hasPaletteByName($paletteName);

    /**
     * Return the palette for the given name.
     *
     * @param string $paletteName The palette name.
     *
     * @return PaletteInterface
     */
    public function getPaletteByName($paletteName);

    /**
     * Create a deep clone of the palette collection.
     *
     * @return void
     */
    public function __clone();
}
