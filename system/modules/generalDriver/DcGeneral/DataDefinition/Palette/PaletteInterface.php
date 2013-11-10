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
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input);

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
	 *
	 * @return PaletteInterface
	 */
	public function addLegends(array $legends);

	/**
	 * Add a legend to this palette.
	 *
	 * @param array|LegendInterface[] $legend
	 *
	 * @return PaletteInterface
	 */
	public function addLegend(LegendInterface $legend);

	/**
	 * Remove a legend from this palette.
	 *
	 * @param array|LegendInterface[] $legend
	 *
	 * @return PaletteInterface
	 */
	public function removeLegend(LegendInterface $legend);

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
