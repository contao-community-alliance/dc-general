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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition
	as PalettePropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyTrueCondition
	as PalettePropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollection;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Class LegacyPalettesParser.
 *
 * This class parses the palettes from a legacy DCA into the palette collection definitions being used in DcGeneral.
 *
 * @package DcGeneral\Contao\Dca\Palette
 */
class LegacyPalettesParser
{
	/**
	 * Parse the palette and sub palette array and create a complete palette collection.
	 *
	 * @param array(string => string)         $palettes    The palettes from the DCA.
	 *
	 * @param array(string => string)         $subpalettes The sub palettes from the DCA [optional].
	 *
	 * @param PaletteCollectionInterface|null $collection  The palette collection to populate [optional].
	 *
	 * @return PaletteCollectionInterface
	 */
	public function parse(array $palettes, array $subpalettes = array(), PaletteCollectionInterface $collection = null)
	{
		if (isset($palettes['__selector__']))
		{
			$selectorFieldNames = $palettes['__selector__'];
			unset($palettes['__selector__']);
		}
		else
		{
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
	 * Parse the given palettes.
	 *
	 * @param array(string => string)    $palettes             The array of palettes, e.g.
	 *                                                         <code>array('default' => '{title_legend},title')</code>.
	 *
	 * @param array(string => string)    $subPaletteProperties Mapped array from subpalette [optional].
	 *
	 * @param array                      $selectorFieldNames   List of names of the properties to be used as selector
	 *                                                         [optional].
	 *
	 * @param PaletteCollectionInterface $collection           The palette collection to populate [optional].
	 *
	 * @return PaletteCollectionInterface
	 */
	public function parsePalettes(
		array $palettes,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(),
		PaletteCollectionInterface $collection = null
	)
	{
		if (!$collection)
		{
			$collection = new PaletteCollection();
		}

		if (isset($palettes['__selector__']))
		{
			$selectorFieldNames = array_merge($selectorFieldNames, $palettes['__selector__']);
			$selectorFieldNames = array_unique($selectorFieldNames);
			unset($palettes['__selector__']);
		}

		foreach ($palettes as $selector => $fields)
		{
			// Fields list must be a string.
			if (!is_string($fields))
			{
				continue;
			}

			if ($collection->hasPaletteByName($selector))
			{
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
	 * Parse a single palette.
	 *
	 * @param string                             $paletteSelector      The selector for the palette.
	 *
	 * @param string                             $fields               The fields contained within the palette.
	 *
	 * @param array(string => PropertyInterface) $subPaletteProperties The sub palette properties [optional].
	 *
	 * @param array(string)                      $selectorFieldNames   The names of all properties being used as
	 *                                                                 selectors [optional].
	 *
	 * @param PaletteInterface                   $palette              The palette to be populated [optional].
	 *
	 * @return Palette
	 */
	public function parsePalette(
		$paletteSelector,
		$fields,
		array $subPaletteProperties = array(),
		array $selectorFieldNames = array(),
		PaletteInterface $palette = null
	)
	{
		if (!$palette)
		{
			$palette = new Palette();
			$palette->setName($paletteSelector);
		}

		$condition = $this->createPaletteCondition($paletteSelector, $selectorFieldNames);
		$palette->setCondition($condition);

		// We ignore the difference between field set (separated by ";") and fields (separated by ",").
		$fields = preg_split('~[;,]~', $fields);
		$fields = array_map('trim', $fields);
		$fields = array_filter($fields);

		$legend = null;

		foreach ($fields as $field)
		{
			// TODO what about :hide? this is currently not supported by LegendInterface.
			if (preg_match('~^\{(.*?)(_legend)?(:hide)?\}$~', $field, $matches))
			{
				$name = $matches[1];
				if ($palette->hasLegend($name))
				{
					$legend = $palette->getLegend($name);
				}
				else
				{
					$legend = new Legend($matches[1]);
					$palette->addLegend($legend);
				}
			}
			else {
				// Fallback for incomplete palettes without legend,
				// Create an empty legend.
				if (!$legend)
				{
					$name = 'unnamed';
					if ($palette->hasLegend($name))
					{
						$legend = $palette->getLegend($name);
					}
					else
					{
						$legend = new Legend($matches[1]);
						$palette->addLegend($legend);
					}
				}

				// Add the current field to the legend.
				$property = new Property($field);
				$legend->addProperty($property);

				// Add sub palette fields to the legend.
				if (isset($subPaletteProperties[$field]))
				{
					foreach ($subPaletteProperties[$field] as $property)
					{
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
	 * @param string $paletteSelector    Create the condition for the selector.
	 *
	 * @param array  $selectorFieldNames The property names to be used as selectors.
	 *
	 * @return PaletteConditionInterface
	 */
	public function createPaletteCondition($paletteSelector, array $selectorFieldNames)
	{
		if ($paletteSelector == 'default')
		{
			return new DefaultPaletteCondition();
		}

		// Legacy fallback, try to split on $selectors with optimistic suggestion of values.
		if (strpos($paletteSelector, '|') === false)
		{
			foreach ($selectorFieldNames as $selectorFieldName)
			{
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

		foreach ($paletteSelectorParts as $paletteSelectorPart)
		{
			// The part is a property name (checkbox like selector).
			if (in_array($paletteSelectorPart, $selectorFieldNames))
			{
				$condition->addCondition(
					new PalettePropertyTrueCondition($paletteSelectorPart)
				);
			}
			// The part is a value (but which?) (select box like selector).
			else
			{
				$orCondition = new PaletteConditionChain(array(), PaletteConditionChain::OR_CONJUNCTION);

				foreach ($selectorFieldNames as $selectorFieldName)
				{
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
		}

		return $condition;
	}

	/**
	 * Parse the sub palettes and return the properties for each selector property.
	 *
	 * @param array $subpalettes        The sub palettes to parse.
	 *
	 * @param array $selectorFieldNames Names of the selector properties [optional].
	 *
	 * @return array(string => PropertyInterface[])
	 */
	public function parseSubpalettes(array $subpalettes, array $selectorFieldNames = array())
	{
		$properties = array();

		foreach ($subpalettes as $subPaletteSelector => $childFields)
		{
			// Child fields list must be a string.
			if (!is_string($childFields))
			{
				continue;
			}

			$selectorFieldName = $this->createSubpaletteSelectorFieldName($subPaletteSelector, $selectorFieldNames);

			$properties[$selectorFieldName] = $this->parseSubpalette(
				$subPaletteSelector,
				$childFields,
				$selectorFieldNames
			);
		}

		return $properties;
	}

	/**
	 * Parse the list of sub palette fields into an array of properties.
	 *
	 * @param string $subPaletteSelector The selector in use.
	 *
	 * @param string $childFields        List of the properties for the sub palette.
	 *
	 * @param array  $selectorFieldNames List of the selector properties [optional].
	 *
	 * @return PropertyInterface[]
	 */
	public function parseSubpalette($subPaletteSelector, $childFields, array $selectorFieldNames = array())
	{
		$childFields = explode(',', $childFields);
		$childFields = array_map('trim', $childFields);

		$condition = $this->createSubpaletteCondition($subPaletteSelector, $selectorFieldNames);

		$properties = array();

		foreach ($childFields as $childField)
		{
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
	 * @param string $subPaletteSelector The selector being evaluated.
	 *
	 * @param array  $selectorFieldNames The names of the properties to be used as selectors [optional].
	 *
	 * @return string
	 */
	public function createSubpaletteSelectorFieldName($subPaletteSelector, array $selectorFieldNames = array())
	{
		$selectorValues     = explode('_', $subPaletteSelector);
		$selectorFieldName  = array_shift($selectorValues);
		$selectorValueCount = count($selectorValues);
		while ($selectorValueCount)
		{
			if (in_array($selectorFieldName, $selectorFieldNames))
			{
				break;
			}
			$selectorFieldName .= '_' . array_shift($selectorValues);
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
	 * @param string $subPaletteSelector The selector being evaluated.
	 *
	 * @param array  $selectorFieldNames The names of the properties to be used as selectors [optional].
	 *
	 * @return PropertyTrueCondition|PropertyValueCondition|null
	 */
	public function createSubpaletteCondition($subPaletteSelector, array $selectorFieldNames = array())
	{
		$condition = null;

		// Try to find a select value first (case 1).
		$selectorValues     = explode('_', $subPaletteSelector);
		$selectorFieldName  = array_shift($selectorValues);
		$selectorValueCount = count($selectorValues);
		while ($selectorValueCount)
		{
			if (in_array($selectorFieldName, $selectorFieldNames))
			{
				$condition = new PropertyValueCondition($selectorFieldName, implode('_', $selectorValues));
				break;
			}
			$selectorFieldName .= '_' . array_shift($selectorValues);
		}

		// If case 1 was not successful, try implicitly case 2 must apply.
		if (!$condition)
		{
			$condition = new PropertyTrueCondition($subPaletteSelector);
		}

		return $condition;
	}
}
