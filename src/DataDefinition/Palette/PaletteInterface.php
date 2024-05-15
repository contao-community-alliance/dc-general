<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A palette contains a set of properties, grouped by legends.
 */
interface PaletteInterface
{
    /**
     * Set the name of this palette.
     *
     * @param string $name The name.
     *
     * @return PaletteInterface
     */
    public function setName($name);

    /**
     * Return the name of this palette.
     *
     * @return string
     */
    public function getName();

    /**
     * Get all properties from all legends in this palette.
     *
     * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
     * @param PropertyValueBag    $input If given, selectors will be evaluated depending on the input data.
     *
     * @return PropertyInterface[]
     */
    public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null);

    /**
     * Get all properties from all legends in this palette that are visible.
     *
     * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
     * @param PropertyValueBag    $input If given, selectors will be evaluated depending on the input data.
     *
     * @return PropertyInterface[]
     */
    public function getVisibleProperties(ModelInterface $model = null, PropertyValueBag $input = null);

    /**
     * Get all properties from all legends in this palette that are editable.
     *
     * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
     * @param PropertyValueBag    $input If given, selectors will be evaluated depending on the input data.
     *
     * @return PropertyInterface[]
     */
    public function getEditableProperties(ModelInterface $model = null, PropertyValueBag $input = null);

    /**
     * Get a property by name from this palette.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyInterface
     *
     * @throws DcGeneralRuntimeException If the this palette does not contain the property.
     */
    public function getProperty($propertyName);

    /**
     * Clear all legends from this palette.
     *
     * @return PaletteInterface
     */
    public function clearLegends();

    /**
     * Set all legends to this palette.
     *
     * @param list<LegendInterface> $legends The legends.
     *
     * @return PaletteInterface
     */
    public function setLegends(array $legends);

    /**
     * Add all legends to this palette.
     *
     * @param list<LegendInterface> $legends The legends.
     * @param LegendInterface       $before  The legend before which the new legends shall be inserted (optional).
     *
     * @return PaletteInterface
     */
    public function addLegends(array $legends, LegendInterface $before = null);

    /**
     * Determine if a legend with the given name exists in this palette.
     *
     * @param string $name The name of the legend to search for.
     *
     * @return bool
     */
    public function hasLegend($name);

    /**
     * Determine if a legend exists in this palette.
     *
     * @param LegendInterface $legend The legend to be checked.
     *
     * @return bool
     */
    public function containsLegend(LegendInterface $legend);

    /**
     * Add a legend to this palette.
     *
     * @param LegendInterface $legend The legend to add.
     * @param LegendInterface $before The legend before which the new legend shall be inserted (optional).
     *
     * @return PaletteInterface
     */
    public function addLegend(LegendInterface $legend, LegendInterface $before = null);

    /**
     * Remove a legend from this palette.
     *
     * @param LegendInterface $legend The legend to remove.
     *
     * @return PaletteInterface
     */
    public function removeLegend(LegendInterface $legend);

    /**
     * Return the legend with the given name.
     *
     * @param string $name The name of the legend to search for.
     *
     * @return LegendInterface
     *
     * @throws DcGeneralRuntimeException Is thrown if there is no legend found.
     */
    public function getLegend($name);

    /**
     * Return the legends from this palette.
     *
     * @return list<LegendInterface>
     */
    public function getLegends();

    /**
     * Set the condition bound to this palette.
     *
     * @param PaletteConditionInterface|null $condition The condition to be bound to this palette.
     *
     * @return PaletteInterface
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
     *
     * @return void
     */
    public function __clone();
}
