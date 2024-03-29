<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyTrueCondition
    as PalettePropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition
    as PalettePropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollection;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function str_replace;
use function strpos;
use function substr_count;

/**
 * Class LegacyPalettesParser.
 *
 * This class parses the palettes from a legacy DCA into the palette collection definitions being used in DcGeneral.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @psalm-type TPalettesArray=array{default: string, __selector__?: list<string>}
 */
class LegacyPalettesParser
{
    /**
     * Parse the palette and sub palette array and create a complete palette collection.
     *
     * @param TPalettesArray                  $palettes    The palettes from the DCA.
     * @param array<string, string>           $subPalettes The sub palettes from the DCA [optional].
     * @param PaletteCollectionInterface|null $collection  The palette collection to populate [optional].
     *
     * @return PaletteCollectionInterface
     */
    public function parse(array $palettes, array $subPalettes = [], PaletteCollectionInterface $collection = null)
    {
        $selectorFieldNames = $palettes['__selector__'] ?? [];
        unset($palettes['__selector__']);

        return $this->parsePalettes(
            $palettes,
            $this->parseSubpalettes($subPalettes, $selectorFieldNames),
            $selectorFieldNames,
            $collection
        );
    }

    /**
     * Parse the given palettes.
     *
     * @param TPalettesArray                         $palettes             The array of palettes, e.g.
     *                                                                     <code>
     *                                                                     ['default' => '{title_legend},title']
     *                                                                     </code>.
     * @param array<string, list<PropertyInterface>> $subPaletteProperties Mapped array from subpalette [optional].
     * @param list<string>                           $selectorFieldNames   List of names of the properties to be used as
     *                                                                     selector [optional].
     * @param PaletteCollectionInterface|null        $collection           The collection to populate [optional].
     *
     * @return PaletteCollectionInterface
     */
    public function parsePalettes(
        array $palettes,
        array $subPaletteProperties = [],
        array $selectorFieldNames = [],
        ?PaletteCollectionInterface $collection = null
    ) {
        if (!$collection) {
            $collection = new PaletteCollection();
        }

        if (isset($palettes['__selector__'])) {
            $selectorFieldNames = array_merge($selectorFieldNames, $palettes['__selector__']);
            $selectorFieldNames = array_values(array_unique($selectorFieldNames));
        }
        unset($palettes['__selector__']);

        foreach ($palettes as $selector => $fields) {
            // Fields list must be a string.
            /** @psalm-suppress DocblockTypeContradiction - only a contradiction when strict types are active */
            if (!is_string($fields)) {
                continue;
            }

            if ($collection->hasPaletteByName($selector)) {
                $palette = $collection->getPaletteByName($selector);
                $this->parsePalette(
                    $selector,
                    $fields,
                    $subPaletteProperties,
                    $selectorFieldNames,
                    $palette
                );

                continue;
            }

            $palette = $this->parsePalette(
                $selector,
                $fields,
                $subPaletteProperties,
                $selectorFieldNames
            );
            $collection->addPalette($palette);
        }

        return $collection;
    }

    /**
     * Parse a single palette.
     *
     * @param string                                 $paletteSelector      The selector for the palette.
     * @param string                                 $fields               The fields contained within the palette.
     * @param array<string, list<PropertyInterface>> $subPaletteProperties The sub palette properties [optional].
     * @param list<string>                           $selectorFieldNames   The names of all properties being used as
     *                                                                     selectors [optional].
     * @param PaletteInterface|null                  $palette              The palette to be populated [optional].
     *
     * @return PaletteInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function parsePalette(
        $paletteSelector,
        $fields,
        array $subPaletteProperties = [],
        array $selectorFieldNames = [],
        PaletteInterface $palette = null
    ) {
        if (!$palette) {
            $palette = new Palette();
            $palette->setName($paletteSelector);
        }

        $palette->setCondition($this->createPaletteCondition($paletteSelector, $selectorFieldNames));

        // We ignore the difference between field set (separated by ";") and fields (separated by ",").
        $fields = preg_split('~[;,]~', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_filter($fields);

        $legend = null;

        foreach ($fields as $field) {
            if (preg_match('~^\{(.*?)(_legend)?(:hide)?\}$~', $field, $matches)) {
                $name = $matches[1];
                if ($palette->hasLegend($name)) {
                    $legend = $palette->getLegend($name);
                } else {
                    $legend = new Legend($matches[1]);
                    $palette->addLegend($legend);
                }
                if (array_key_exists(3, $matches)) {
                    $legend->setInitialVisibility(false);
                }

                continue;
            }

            // Fallback for incomplete palettes without legend,
            // create an empty legend.
            if (!$legend) {
                $name = 'unnamed';
                if ($palette->hasLegend($name)) {
                    $legend = $palette->getLegend($name);
                } else {
                    $legend = new Legend($name);
                    $palette->addLegend($legend);
                }
            }

            // Add the current field to the legend.
            $property = new Property($field);
            $legend->addProperty($property);

            // Add sub palette fields to the legend.
            if (isset($subPaletteProperties[$field])) {
                foreach ($subPaletteProperties[$field] as $property) {
                    $legend->addProperty(clone $property);
                }
            }
        }

        return $palette;
    }

    /**
     * Parse the palette selector and create the corresponding condition.
     *
     * @param string $paletteSelector    Create the condition for the selector.
     * @param array  $selectorFieldNames The property names to be used as selectors.
     *
     * @return null|PaletteConditionInterface
     */
    public function createPaletteCondition($paletteSelector, array $selectorFieldNames)
    {
        if ('default' === $paletteSelector) {
            return new DefaultPaletteCondition();
        }

        // Legacy fallback, try to split on $selectors with optimistic suggestion of values.
        if (false === strpos($paletteSelector, '|')) {
            foreach ($selectorFieldNames as $selectorFieldName) {
                $paletteSelector = str_replace(
                    $selectorFieldName,
                    '|' . $selectorFieldName . '|',
                    $paletteSelector
                );
            }
        }

        // Extended mode, split selectors and values with "|".
        $paletteSelectorParts = explode('|', $paletteSelector);
        $paletteSelectorParts = array_map('trim', $paletteSelectorParts);
        $paletteSelectorParts = array_filter($paletteSelectorParts);

        $condition = new PaletteConditionChain();

        foreach ($paletteSelectorParts as $paletteSelectorPart) {
            // The part is a property name (checkbox like selector).
            if (in_array($paletteSelectorPart, $selectorFieldNames)) {
                $condition->addCondition(
                    new PalettePropertyTrueCondition($paletteSelectorPart)
                );
                continue;
            }

            // The part is a value (but which?) (select box like selector).
            $orCondition = new PaletteConditionChain([], PaletteConditionChain::OR_CONJUNCTION);

            foreach ($selectorFieldNames as $selectorFieldName) {
                $orCondition->addCondition(
                    new PalettePropertyValueCondition(
                        $selectorFieldName,
                        $paletteSelectorPart,
                        true
                    )
                );
            }

            $condition->addCondition($orCondition);
        }

        return $condition;
    }

    /**
     * Parse the sub palettes and return the properties for each selector property.
     *
     * @param array<string, string> $subpalettes        The sub palettes to parse.
     * @param list<string>          $selectorFieldNames Names of the selector properties [optional].
     *
     * @return array<string, list<PropertyInterface>>
     */
    public function parseSubpalettes(array $subpalettes, array $selectorFieldNames = [])
    {
        $properties = [];
        foreach ($subpalettes as $subPaletteSelector => $childFields) {
            // Child fields list must be a string.
            /** @psalm-suppress DocblockTypeContradiction - only a contradiction when strict types are active */
            if (!is_string($childFields)) {
                continue;
            }

            $selectorFieldName = $this->createSubpaletteSelectorFieldName($subPaletteSelector, $selectorFieldNames);

            $selectorProperty = [];
            // For selectable sub selector.
            if (
                isset($properties[$selectorFieldName])
                && (0 < substr_count($subPaletteSelector, '_'))
            ) {
                $selectorProperty = $properties[$selectorFieldName];
            }

            $properties[$selectorFieldName] = $this->parseSubpalette(
                $subPaletteSelector,
                $childFields,
                $selectorFieldNames,
                $selectorProperty
            );
        }

        return $properties;
    }

    /**
     * Parse the list of sub palette fields into an array of properties.
     *
     * @param string                  $subPaletteSelector The selector in use.
     * @param string                  $childFields        List of the properties for the sub palette.
     * @param list<string>            $selectorFieldNames List of the selector properties [optional].
     * @param list<PropertyInterface> $properties         List of the selector visible properties [optional].
     *
     * @return list<PropertyInterface>
     */
    public function parseSubpalette(
        $subPaletteSelector,
        $childFields,
        array $selectorFieldNames = [],
        array $properties = []
    ) {
        $childFields = explode(',', $childFields);
        $childFields = array_map('trim', $childFields);

        $condition = $this->createSubpaletteCondition($subPaletteSelector, $selectorFieldNames);

        foreach ($childFields as $childField) {
            $property = new Property($childField);
            $property->setVisibleCondition(clone $condition);
            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * Translate a sub palette selector into the real name of a property.
     *
     * This method supports the following cases for the sub palette selector:
     *
     * Case 1: the sub palette selector contain a combination of "property name" + '_' + value
     *         in which we require that the "property name" is contained within $selectorFieldNames.
     *         In this cases a select/radio sub palette is in place.
     *
     * Case 2: the sub palette selector is only a "property name", the value is then implicated to be true.
     *         In this cases a checkbox sub palette is in place.
     *
     * @param string       $subPaletteSelector The selector being evaluated.
     * @param list<string> $selectorFieldNames The names of the properties to be used as selectors [optional].
     *
     * @return string
     */
    public function createSubpaletteSelectorFieldName($subPaletteSelector, array $selectorFieldNames = [])
    {
        $selectorValues     = explode('_', $subPaletteSelector);
        $selectorFieldName  = array_shift($selectorValues);
        $selectorValueCount = count($selectorValues);
        while ($selectorValueCount) {
            if (in_array($selectorFieldName, $selectorFieldNames)) {
                break;
            }
            $selectorFieldName .= '_' . array_shift($selectorValues);
            $selectorValueCount = count($selectorValues);
        }

        return $selectorFieldName;
    }

    /**
     * Parse the sub palette selector and create the corresponding condition.
     *
     * This method supports the following cases for the sub palette selector:
     *
     * Case 1: the sub palette selector contain a combination of "property name" + '_' + value
     *         in which we require that the "property name" is contained within $selectorFieldNames.
     *         In this cases a select/radio sub palette is in place.
     *
     * Case 2: the sub palette selector is only a "property name", the value is then implicated to be true.
     *         In this cases a checkbox sub palette is in place.
     *
     * @param string       $subPaletteSelector The selector being evaluated.
     * @param list<string> $selectorFieldNames The names of the properties to be used as selectors [optional].
     *
     * @return PropertyTrueCondition|PropertyValueCondition
     */
    public function createSubpaletteCondition($subPaletteSelector, array $selectorFieldNames = [])
    {
        $condition = null;

        // Try to find a select value first (case 1).
        $selectorValues     = explode('_', $subPaletteSelector);
        $selectorFieldName  = array_shift($selectorValues);
        $selectorValueCount = count($selectorValues);

        while ($selectorValueCount) {
            if (empty($selectorValues)) {
                break;
            }

            if (in_array($selectorFieldName, $selectorFieldNames)) {
                $condition = new PropertyValueCondition($selectorFieldName, implode('_', $selectorValues));
                break;
            }
            $selectorFieldName .= '_' . array_shift($selectorValues);
        }

        // If case 1 was not successful, try implicitly case 2 must apply.
        if (!$condition) {
            $condition = new PropertyTrueCondition($subPaletteSelector);
        }

        return $condition;
    }
}
