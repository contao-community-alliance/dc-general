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
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A palette contains a set of properties, grouped by legends.
 */
interface PaletteInterface
{
	/**
	 * Set the name of this palette.
	 *
	 * @deprecated Only for backwards compatibility, we will remove palette names in the future!
	 *
	 * @return PaletteInterface
	 */
	public function setName($name);

	/**
	 * Return the name of this palette.
	 *
	 * @deprecated Only for backwards compatibility, we will remove palette names in the future!
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get all properties from all legends in this palette.
	 *
	 * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
	 * @param PropertyValueBag $input If given, selectors will be evaluated depending on the input data.
	 *
	 * @return PropertyInterface[]
	 */
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null);


	/**
	 * Get all properties from all legends in this palette that are visible.
	 *
	 * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
	 * @param PropertyValueBag $input If given, selectors will be evaluated depending on the input data.
	 *
	 * @return PropertyInterface[]
	 */
	public function getVisibleProperties(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Get all properties from all legends in this palette that are editable.
	 *
	 * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
	 * @param PropertyValueBag $input If given, selectors will be evaluated depending on the input data.
	 *
	 * @return PropertyInterface[]
	 */
	public function getEditableProperties(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Clear all legends from this palette.
	 *
	 * @return PaletteInterface
	 */
	public function clearLegends();

	/**
	 * Set all legends to this palette.
	 *
	 * @param array|LegendInterface[] $legends
	 *
	 * @return PaletteInterface
	 */
	public function setLegends(array $legends);

	/**
	 * Add all legends to this palette.
	 *
	 * @param array|LegendInterface[] $legends
	 * @param LegendInterface $before
	 *
	 * @return PaletteInterface
	 */
	public function addLegends(array $legends, LegendInterface $before = null);

	/**
	 * Determine if a legend with the given name exists in this palette.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasLegend($name);

	/**
	 * Determine if a legend exists in this palette.
	 *
	 * @param LegendInterface $legend
	 *
	 * @return bool
	 */
	public function containsLegend(LegendInterface $legend);

	/**
	 * Add a legend to this palette.
	 *
	 * @param LegendInterface $legend
	 * @param LegendInterface $before
	 *
	 * @return PaletteInterface
	 */
	public function addLegend(LegendInterface $legend, LegendInterface $before = null);

	/**
	 * Remove a legend from this palette.
	 *
	 * @param LegendInterface $legend
	 *
	 * @return PaletteInterface
	 */
	public function removeLegend(LegendInterface $legend);

	/**
	 * Return the legend with the given name.
	 *
	 * @param string $name
	 *
	 * @return LegendInterface
	 *
	 * @throws DcGeneralRuntimeException Is thrown if there is no legend found.
	 */
	public function getLegend($name);

	/**
	 * Return the legends from this palette.
	 *
	 * @return array|LegendInterface[]
	 */
	public function getLegends();

	/**
	 * Set the condition bound to this palette.
	 *
	 * @param PaletteConditionInterface|null $condition
	 */
	public function setCondition(PaletteConditionInterface $condition = null);

	/**
	 * Get the condition bound to this palette.
	 *
	 * @return PaletteConditionInterface|null
	 */
	public function getCondition();

	/**
	 * Create a deep clone of the palette.
	 */
	public function __clone();
}
