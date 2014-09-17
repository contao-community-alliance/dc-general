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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of PaletteCollectionInterface.
 */
class PaletteCollection implements PaletteCollectionInterface
{
    /**
     * The palettes contained in the collection.
     *
     * @var array|PaletteInterface[]
     */
    protected $palettes = array();

    /**
     * Remove all palettes from this collection.
     *
     * @return PaletteCollectionInterface
     */
    public function clearPalettes()
    {
        $this->palettes = array();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPalettes(array $palettes)
    {
        $this->clearPalettes();
        $this->addPalettes($palettes);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPalettes(array $palettes)
    {
        foreach ($palettes as $palette)
        {
            $this->addPalette($palette);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPalette(PaletteInterface $palette)
    {
        $hash = spl_object_hash($palette);

        $this->palettes[$hash] = $palette;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePalette(PaletteInterface $palette)
    {
        $hash = spl_object_hash($palette);
        unset($this->palettes[$hash]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPalettes()
    {
        return array_values($this->palettes);
    }

    /**
     * {@inheritdoc}
     */
    public function hasPalette(PaletteInterface $palette)
    {
        $hash = spl_object_hash($palette);
        return isset($this->palettes[$hash]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException Is thrown if there is no palette found or more than one palette.
     */
    public function findPalette(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        $matches = array();

        // Determinate the matching count for each palette.
        foreach ($this->palettes as $palette)
        {
            $condition = $palette->getCondition();

            if ($condition)
            {
                $count = $condition->getMatchCount($model, $input);

                if ($count !== false)
                {
                    $matches[$count][] = $palette;
                }
            }
        }

        // Sort by count.
        ksort($matches);

        // Get palettes with highest matching count.
        $palettes = array_pop($matches);

        if (count($palettes) !== 1)
        {
            throw new DcGeneralInvalidArgumentException(sprintf('%d matching palettes found.', count($palettes)));
        }

        return $palettes[0];
    }

    /**
     * {@inheritdoc}
     */
    public function hasPaletteByName($paletteName)
    {
        foreach ($this->palettes as $palette)
        {
            if ($palette->getName() == $paletteName)
            {
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
        foreach ($this->palettes as $palette)
        {
            if ($palette->getName() == $paletteName)
            {
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
        $palettes = array();
        foreach ($this->palettes as $index => $palette)
        {
            $palettes[$index] = clone $palette;
        }
        $this->palettes = $palettes;
    }
}
