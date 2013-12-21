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

/**
 * A legend group a lot of properties.
 */
interface LegendInterface
{
	/**
	 * Return the palette this legend belongs to.
	 *
	 * @param PaletteInterface|null $palette The palette.
	 *
	 * @return LegendInterface
	 */
	public function setPalette(PaletteInterface $palette = null);

	/**
	 * Return the palette this legend belongs to.
	 *
	 * @return PaletteInterface|null
	 */
	public function getPalette();

	/**
	 * Set the name of this legend (e.g. "title", not "title_legend").
	 *
	 * @param string $name The name.
	 *
	 * @return LegendInterface
	 */
	public function setName($name);

	/**
	 * Return the name of this legend (e.g. "title", not "title_legend").
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Set if this legend's initial state is visible (expanded).
	 *
	 * @param bool $value The visibility state.
	 *
	 * @return bool
	 */
	public function setInitialVisibility($value);

	/**
	 * Determine if this legend's initial state shall be expanded.
	 *
	 * @return LegendInterface
	 */
	public function isInitialVisible();

	/**
	 * Clear all properties from this legend.
	 *
	 * @return LegendInterface
	 */
	public function clearProperties();

	/**
	 * Set the properties of this legend.
	 *
	 * @param array|PropertyInterface[] $properties The properties.
	 *
	 * @return LegendInterface
	 */
	public function setProperties(array $properties);

	/**
	 * Add all properties to this legend.
	 *
	 * @param array|PropertyInterface[] $properties The properties.
	 *
	 * @param PropertyInterface         $before     The property before the passed properties shall be inserted
	 *                                              (optional).
	 *
	 * @return LegendInterface
	 */
	public function addProperties(array $properties, PropertyInterface $before = null);

	/**
	 * Add a property to this legend.
	 *
	 * @param PropertyInterface $property The property.
	 *
	 * @param PropertyInterface $before   The property before the passed property shall be inserted (optional).
	 *
	 * @return LegendInterface
	 */
	public function addProperty(PropertyInterface $property, PropertyInterface $before = null);

	/**
	 * Remove a property from this legend.
	 *
	 * @param PropertyInterface $property The property.
	 *
	 * @return LegendInterface
	 */
	public function removeProperty(PropertyInterface $property);

	/**
	 * Get all properties in this legend.
	 *
	 * @param ModelInterface|null $model If given, subpalettes will be evaluated depending on the model.
	 *                                   If no model is given, all properties will be returned, including subpalette
	 *                                   properties.
	 *
	 * @param PropertyValueBag    $input If given, subpalettes will be evaluated depending on the input data.
	 *                                   If no model and no input data is given, all properties will be returned,
	 *                                   including subpalette properties.
	 *
	 * @return PropertyInterface[]
	 */
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Create a deep clone of the legend.
	 *
	 * @return void
	 */
	public function __clone();
}
