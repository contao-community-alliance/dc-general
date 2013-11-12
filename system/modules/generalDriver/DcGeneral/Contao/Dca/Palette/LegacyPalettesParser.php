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

namespace DcGeneral\Contao\Dca\Palette;

use DcGeneral\Contao\Dca\Container;
use DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition as PalettePropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyTrueCondition as PalettePropertyTrueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Legend;
use DcGeneral\DataDefinition\Palette\Palette;
use DcGeneral\DataDefinition\Palette\PaletteCollection;
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\DataDefinition\Palette\PaletteInterface;
use DcGeneral\DataDefinition\Palette\Property;
use DcGeneral\DataDefinition\Palette\PropertyInterface;

class LegacyPalettesParser
{
	/**
	 * Parse the palette and subpalette array and create a complete palette collection.
	 *
	 * @param array <string, string>      $palettes
	 * @param array <string, string>      $subpalettes
	 *
	 * @return PaletteCollectionInterface
	 */
	public function parse(array $palettes, array $subpalettes = array(), PaletteCollectionInterface $collection = null)
	{
		if (isset($palettes['__selector__'])) {
			$selectorFieldNames = $palettes['__selector__'];
			unset($palettes['__selector__']);
		}
		else {
			$selectorFieldNames = array();
		}

		$subPaletteProperties = $this->parseSubpalettes($subpalettes, $selectorFieldNames);

		return $this->parsePalettes(
			$palettes,
			$subPaletteProperties,
			$selectorFieldNames,
			$collection
		);
	}

	/**
	 * @param                            array <string, string> $palettes The array of palettes,
	 *                                         e.g. <code>array('default' => '{title_legend},title')</code>
	 * @param                            array <string, PropertyInterface[]> $subPaletteProperties Mapped array from
	 *                                         subpalette
	 * @param array                      $selectorFieldNames
	 *
	 * @return PaletteCollectionInterface
	 */
	public function parsePalettes(
		array $palettes,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(), PaletteCollectionInterface $collection = null
	) {
		if (!$collection) {
			$collection = new PaletteCollection();
		}

		if (isset($palettes['__selector__'])) {
			$selectorFieldNames = array_merge($selectorFieldNames, $palettes['__selector__']);
			$selectorFieldNames = array_unique($selectorFieldNames);
			unset($palettes['__selector__']);
		}

		foreach ($palettes as $selector => $fields) {
			// fields list must be a string
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
			}
			else {
				$palette = $this->parsePalette(
					$selector,
					$fields,
					$subPaletteProperties,
					$selectorFieldNames
				);
				$collection->addPalette($palette);
			}
		}

		return $collection;
	}

	/**
	 * @param string $paletteSelector
	 * @param string $fields
	 * @param        array <string, PropertyInterface>  $subPaletteProperties
	 * @param        array <string>  $selectorFieldNames
	 *
	 * @return Palette
	 */
	public function parsePalette(
		$paletteSelector,
		$fields,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(),
		PaletteInterface $palette = null
	) {
		if (!$palette) {
			$palette = new Palette();
			$palette->setName($paletteSelector);
		}

		$condition = $this->createPaletteCondition($paletteSelector, $selectorFieldNames);
		$palette->setCondition($condition);

		// we ignore the difference between fieldset (separated by ";") and fields (separated by ",")
		$fields = preg_split('~[;,]~', $fields);
		$fields = array_map('trim', $fields);
		$fields = array_filter($fields);

		$legend = null;

		foreach ($fields as $field) {
			// TODO what about :hide? this is currently not supported by LegendInterface
			if (preg_match('~^\{(.*?)(_legend)?(:hide)?\}$~', $field, $matches)) {
				$name = $matches[1];
				if ($palette->hasLegend($name)) {
					$legend = $palette->getLegend($name);
				}
				else {
					$legend = new Legend($matches[1]);
					$palette->addLegend($legend);
				}
			}
			else {
				// fallback for incomplete palettes without legend,
				// create an empty legend
				if (!$legend) {
					$name = 'unnamed';
					if ($palette->hasLegend($name)) {
						$legend = $palette->getLegend($name);
					}
					else {
						$legend = new Legend($matches[1]);
						$palette->addLegend($legend);
					}
				}

				// add the current field to the legend
				$property = new Property($field);
				$legend->addProperty($property);

				// add subpalette fields to the legend
				if (isset($subPaletteProperties[$field])) {
					foreach ($subPaletteProperties[$field] as $property) {
						$legend->addProperty(clone $property);
					}
				}
			}
		}

		return $palette;
	}

	/**
	 * Parse the palette selector and create the corresponding condition.
	 *
	 * @param string $paletteSelector
	 * @param array  $selectorFieldNames
	 *
	 * @return PaletteConditionInterface
	 */
	public function createPaletteCondition($paletteSelector, array $selectorFieldNames)
	{
		if ($paletteSelector == 'default') {
			return new DefaultPaletteCondition();
		}

		else {
			// legacy fallback, try to split on $selectors with optimistic suggestion of values
			if (strpos($paletteSelector, '|') === false) {
				foreach ($selectorFieldNames as $selectorFieldName) {
					$paletteSelector = str_replace(
						$selectorFieldName,
						'|' . $selectorFieldName . '|',
						$paletteSelector
					);
				}
			}

			// extended mode, split selectors and values with "|"
			$paletteSelectorParts = explode('|', $paletteSelector);
			$paletteSelectorParts = array_map('trim', $paletteSelectorParts);
			$paletteSelectorParts = array_filter($paletteSelectorParts);

			$condition = new PaletteConditionChain();

			foreach ($paletteSelectorParts as $paletteSelectorPart) {
				// the part is a property name (checkbox like selector)
				if (in_array($paletteSelectorPart, $selectorFieldNames)) {
					$condition->addCondition(
						new PalettePropertyTrueCondition($paletteSelectorPart)
					);
				}

				// the part is a value (but which?) (select box like selector)
				else {
					$orCondition = new PaletteConditionChain(PaletteConditionChain::OR_CONJUNCTION);

					foreach ($selectorFieldNames as $selectorFieldName) {
						$orCondition->addCondition(
							new PalettePropertyValueCondition(
								$selectorFieldName,
								$paletteSelectorPart
							)
						);
					}

					$condition->addCondition($orCondition);
				}
			}

			return $condition;
		}
	}

	/**
	 * @param array $subpalettes
	 *
	 * @return array<string, PropertyInterface[]>
	 */
	public function parseSubpalettes(array $subpalettes, array $selectorFieldNames = array())
	{
		$properties = array();

		foreach ($subpalettes as $subPaletteSelector => $childFields) {
			// child fields list must be a string
			if (!is_string($childFields)) {
				continue;
			}

			// build field name for subpalette selector
			// case 1: the subpalette selector contain a combination of "field name" + value
			//         require that the "field name" is in $selectors
			// case 2: the subpalette selector is only a "field name", the value is implicated as true
			$selectorValues    = explode('_', $subPaletteSelector);
			$selectorFieldName = array_shift($selectorValues);
			while (count($selectorValues)) {
				if (in_array($selectorFieldName, $selectorFieldNames)) {
					break;
				}
				$selectorFieldName .= '_' . array_shift($selectorValues);
			}

			$properties[$selectorFieldName] = $this->parseSubpalette(
				$subPaletteSelector,
				$childFields,
				$selectorFieldNames
			);
		}

		return $properties;
	}

	/**
	 * Parse the list of subpalette fields into an array of properties.
	 *
	 * @param string $subPaletteSelector
	 * @param string $childFields
	 * @param array  $selectorFieldNames
	 *
	 * @return PropertyInterface[]
	 */
	public function parseSubpalette($subPaletteSelector, $childFields, array $selectorFieldNames = array())
	{
		$childFields = explode(',', $childFields);
		$childFields = array_map('trim', $childFields);

		// build basic condition for the subpalette selector
		// case 1: the subpalette selector contain a combination of "field name" + value
		//         require that the "field name" is in $selectorFieldNames
		//         -> select/radio type
		// case 2: the subpalette selector is only a "field name", the value is implicated as true
		//         -> checkbox type
		$condition = null;

		// try case 1
		$selectorValues    = explode('_', $subPaletteSelector);
		$selectorFieldName = array_shift($selectorValues);
		while (count($selectorValues)) {
			if (in_array($selectorFieldName, $selectorFieldNames)) {
				$condition = new PropertyValueCondition($selectorFieldName, implode('_', $selectorValues));
				break;
			}
			$selectorFieldName .= '_' . array_shift($selectorValues);
		}

		// if case 1 not passed, try case 2
		if (!$condition) {
			$condition = new PropertyTrueCondition($subPaletteSelector);
		}

		$properties = array();

		foreach ($childFields as $childField) {
			$property = new Property($childField);
			$property->setVisibleCondition(clone $condition);
			$properties[] = $property;
		}

		return $properties;
	}
}
