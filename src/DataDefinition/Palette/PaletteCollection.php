<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of PaletteCollectionInterface.
 */
class PaletteCollection implements PaletteCollectionInterface
{
    /**
     * The palettes contained in the collection.
     *
     * @var array<string, PaletteInterface>
     */
    protected $palettes = [];

    /**
     * Remove all palettes from this collection.
     *
     * @return PaletteCollectionInterface
     */
    public function clearPalettes()
    {
        $this->palettes = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPalettes(array $palettes)
    {
        $this->clearPalettes()->addPalettes($palettes);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPalettes(array $palettes)
    {
        foreach ($palettes as $palette) {
            $this->addPalette($palette);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPalette(PaletteInterface $palette)
    {
        $this->palettes[\spl_object_hash($palette)] = $palette;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePalette(PaletteInterface $palette)
    {
        unset($this->palettes[\spl_object_hash($palette)]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPalettes()
    {
        return \array_values($this->palettes);
    }

    /**
     * {@inheritdoc}
     */
    public function hasPalette(PaletteInterface $palette)
    {
        return isset($this->palettes[\spl_object_hash($palette)]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException Is thrown if there is no palettes found.
     * @throws DcGeneralInvalidArgumentException Is thrown if there is no palette found or more than one palette.
     */
    public function findPalette(ModelInterface $model = null, PropertyValueBagInterface $input = null)
    {
        $matches = [];

        // Determinate the matching count for each palette.
        foreach ($this->palettes as $palette) {
            $condition = $palette->getCondition();

            if ($condition) {
                // We should have defined the interfaces back in 2013... :/
                assert($input === null || $input instanceof PropertyValueBag);
                $count = $condition->getMatchCount($model, $input);

                if (false !== $count) {
                    $matches[$count][] = $palette;
                }
            }
        }

        // Sort by count.
        \ksort($matches);

        // Get palettes with highest matching count.
        $palettes = \array_pop($matches);

        if (null === $palettes) {
            throw new DcGeneralInvalidArgumentException('No matching palettes found.');
        }
        if (1 !== \count($palettes)) {
            throw new DcGeneralInvalidArgumentException(\sprintf('%d matching palettes found.', \count($palettes)));
        }

        return $palettes[0];
    }

    /**
     * {@inheritdoc}
     */
    public function hasPaletteByName($paletteName)
    {
        foreach ($this->palettes as $palette) {
            if ($paletteName === $palette->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException Is thrown if there is no palette with this name.
     */
    public function getPaletteByName($paletteName)
    {
        foreach ($this->palettes as $palette) {
            if ($paletteName === $palette->getName()) {
                return $palette;
            }
        }

        throw new DcGeneralInvalidArgumentException('No palette found for name ' . $paletteName);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $palettes = [];
        foreach ($this->palettes as $index => $palette) {
            $palettes[$index] = clone $palette;
        }
        $this->palettes = $palettes;
    }
}
