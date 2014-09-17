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

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * A property contained within a palette.
 *
 * @package DcGeneral\DataDefinition\Palette
 */
interface PropertyInterface
{
    /**
     * Set the name of the property.
     *
     * @param string $name The name of the property.
     *
     * @return PropertyInterface
     */
    public function setName($name);

    /**
     * Return the name of the property.
     *
     * @return string
     */
    public function getName();

    /**
     * Check the conditions, if this property is visible.
     *
     * @param ModelInterface|null $model  If given, sub palettes will be evaluated depending on the model.
     *                                    If no model is given, all properties will be returned, including sub palette
     *                                    properties.
     *
     * @param PropertyValueBag    $input  If given, sub palettes will be evaluated depending on the input data.
     *                                    If no model and no input data is given, all properties will be returned,
     *                                    including sub palette properties.
     *
     * @param LegendInterface     $legend The legend the property is assigned to.
     *
     * @return bool
     */
    public function isVisible(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        LegendInterface $legend = null
    );

    /**
     * Check the conditions, if this property is editable.
     *
     * @param ModelInterface|null $model  If given, sub palettes will be evaluated depending on the model.
     *                                    If no model is given, all properties will be returned, including sub palette
     *                                    properties.
     *
     * @param PropertyValueBag    $input  If given, sub palettes will be evaluated depending on the input data.
     *                                    If no model and no input data is given, all properties will be returned,
     *                                    including sub palette properties.
     *
     * @param LegendInterface     $legend The legend the property is assigned to.
     *
     * @return bool
     */
    public function isEditable(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        LegendInterface $legend = null
    );

    /**
     * Set the visible condition for this property.
     *
     * @param PropertyConditionInterface $condition The condition.
     *
     * @return PropertyInterface
     */
    public function setVisibleCondition(PropertyConditionInterface $condition = null);

    /**
     * Get the visible condition for this property.
     *
     * @return PropertyConditionInterface
     */
    public function getVisibleCondition();

    /**
     * Set the editable condition for this property.
     *
     * @param PropertyConditionInterface $condition The condition.
     *
     * @return PropertyInterface
     */
    public function setEditableCondition(PropertyConditionInterface $condition = null);

    /**
     * Get the editable condition for this property.
     *
     * @return PropertyConditionInterface
     */
    public function getEditableCondition();

    /**
     * Create a deep clone of the property.
     *
     * @return void
     */
    public function __clone();
}
