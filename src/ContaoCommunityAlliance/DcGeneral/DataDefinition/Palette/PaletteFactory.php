<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * The palette factory.
 *
 * @deprecated This class is deprecated for the moment, use the PaletteBuilder instead.
 */
class PaletteFactory
{
    /**
     * Create a new palette collection from a list of palettes.
     *
     * @param array|PaletteInterface $palettes A list of palettes. Can be multiple arrays and arrays of arrays.
     *
     *
     * @param PaletteInterface       $_        A list of palettes. Can be multiple arrays and arrays of arrays.
     *
     * @return PaletteCollectionInterface
     */
    public static function createPaletteCollection($palettes, $_ = null)
    {
        $collection = new PaletteCollection();

        $args = func_get_args();
        static::fillPaletteCollection($collection, $args);
        return $collection;
    }

    /**
     * Fill a palette collection from a multidimensional array of palettes.
     *
     * @param PaletteCollection $collection The collection.
     *
     * @param array             $palettes   The palettes to fill.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When an invalid palette has been passed.
     */
    public static function fillPaletteCollection(PaletteCollection $collection, array $palettes)
    {
        foreach ($palettes as $palette) {
            if ($palette instanceof PaletteInterface) {
                $collection->addPalette($palette);
            } elseif (is_array($palette)) {
                static::fillPaletteCollection($collection, $palette);
            } else {
                $type = is_object($palette) ? get_class($palette) : gettype($palette);
                throw new DcGeneralInvalidArgumentException(
                    'Palette [' . $type . '] does not implement PaletteInterface'
                );
            }
        }
    }

    /**
     * Create a new palette from a list of legends.
     *
     * @param string $name   The name of the palette, can be omitted (deprecated).
     *
     * @param array  $legend A list of legends. Can be multiple arrays and arrays of arrays.
     *
     * @param array  $_      A list of legends. Can be multiple arrays and arrays of arrays.
     *
     * @return PaletteInterface
     */
    public static function createPalette($name = null, $legend = null, $_ = null)
    {
        $palette = new Palette();

        $args = func_get_args();

        if (is_string($args[0])) {
            $name = array_shift($args);
            $palette->setName($name);
        }

        static::fillPalette($palette, $args);

        return $palette;
    }

    /**
     * Fill a palette from a multidimensional array of legends.
     *
     * @param PaletteInterface $palette The palette.
     *
     * @param array            $legends The legends.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When an invalid legend has been passed.
     */
    public static function fillPalette(PaletteInterface $palette, array $legends)
    {
        foreach ($legends as $legend) {
            if ($legend instanceof LegendInterface) {
                $palette->addLegend($legend);
            } elseif (is_array($legend)) {
                static::fillPalette($palette, $legend);
            } else {
                $type = is_object($legend) ? get_class($legend) : gettype($legend);
                throw new DcGeneralInvalidArgumentException(
                    'Legend [' . $type . '] does not implement LegendInterface'
                );
            }
        }
    }

    /**
     * Create a new legend from a list of properties.
     *
     * @param string                  $name     The name of the legend.
     *
     * @param array|PropertyInterface $property A list of properties. Can be multiple arrays and arrays of arrays.
     *
     * @param PropertyInterface       $_        A list of properties. Can be multiple arrays and arrays of arrays.
     *
     * @return LegendInterface
     */
    public static function createLegend($name, $property = null, $_ = null)
    {
        $legend = new Legend();
        $legend->setName($name);

        $args = func_get_args();
        // Drop the name from argument list.
        array_shift($args);

        static::fillLegend($legend, $args);

        return $legend;
    }

    /**
     * Fill a legend from a multidimensional array of properties.
     *
     * @param LegendInterface $legend     The legend.
     *
     * @param array           $properties The properties.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When an invalid property is encountered.
     */
    public static function fillLegend(LegendInterface $legend, array $properties)
    {
        foreach ($properties as $property) {
            if ($property instanceof PropertyInterface) {
                $legend->addProperty($property);
            } elseif (is_array($property)) {
                static::fillLegend($legend, $property);
            } else {
                $type = is_object($property) ? get_class($property) : gettype($property);
                throw new DcGeneralInvalidArgumentException(
                    'Property [' . $type . '] does not implement PropertyInterface'
                );
            }
        }
    }
}
