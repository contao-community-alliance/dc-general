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
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition as PalettePropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyTrueCondition as PalettePropertyTrueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Legend;
use DcGeneral\DataDefinition\Palette\Palette;
use DcGeneral\DataDefinition\Palette\PaletteCollection;
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\DataDefinition\Palette\Property;
use DcGeneral\DataDefinition\Palette\PropertyInterface;

class LegacyPalettesParser
{
	public function parse(array $palettes, array $subpalettes = array(), PaletteCollectionInterface $collection = null)
	{
		if (isset($palettes['__selector__'])) {
			$selectorFieldNames = $palettes['__selector__'];
			unset($palettes['__selector__']);
		}
		else {
			$selectorFieldNames = array();
		}

		$subpaletteProperties = $this->parseSubpalettes($subpalettes, $selectorFieldNames);
	}

	/**
	 * @param                            array <string, string> $palettes The array of palettes,
	 *                                         e.g. <code>array('default' => '{title_legend},title')</code>
	 * @param                            array <string, PropertyInterface[]> $subPaletteProperties Mapped array from
	 *                                         subpalette
	 * @param array                      $selectorFieldNames
	 * @param PaletteCollectionInterface $collection
	 *
	 * @return PaletteCollectionInterface
	 */
	public function parsePalettes(
		array $palettes,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(),
		PaletteCollectionInterface $collection = null
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

		}

		return $collection;
	}

	/**
	 * @param string $paletteSelector
	 * @param string $fields
	 * @param array  $subPaletteProperties
	 * @param array  $selectorFieldNames
	 *
	 * return Palette
	 */
	public function parsePalette(
		$paletteSelector,
		$fields,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(),
		Palette $palette = null
	) {
		if (!$palette) {
			$palette = new Palette();
		}

		// this is the default palette
		if ($paletteSelector == 'default') {
			$palette->setCondition(new DefaultPaletteCondition());
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

			$palette->setCondition($condition);
		}

		$fields = preg_split('~[;,]~', $fields);
		$fields = array_map('trim', $fields);
		$fields = array_filter($fields);

		$legend = null;

		foreach ($fields as $field) {
			if (preg_match('~^\{(.*?)(_legend)?(:hide)?\}$~', $field, $matches)) {
				$legend = new Legend($matches[1]);
			}
			else {
				// fallback for incomplete palettes without legend,
				// create an empty legend
				if (!$legend) {
					$legend = new Legend('unnamed');
					$palette->addLegend($legend);
				}

				$legend->addProperty()
			}
		}

		return $palette;
	}

	/**
	 * @param array $subpalettes
	 *
	 * return array<string, PropertyInterface[]>
	 */
	public function parseSubpalettes(array $subpalettes, array $selectorFieldNames = array())
	{
		$properties = array();

		foreach ($subpalettes as $subPaletteSelector => $childFields) {
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
	 *
	 * return PropertyInterface[]
	 */
	public function parseSubpalette($subPaletteSelector, $childFields, array $selectorFieldNames = array())
	{
		$childFields = explode(',', $childFields);
		$childFields = array_map('trim', $childFields);

		// build basic condition for the subpalette selector
		// case 1: the subpalette selector contain a combination of "field name" + value
		//         require that the "field name" is in $selectors
		// case 2: the subpalette selector is only a "field name", the value is implicated as true
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
