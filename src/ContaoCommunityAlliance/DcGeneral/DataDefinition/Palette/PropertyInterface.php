<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * A property contained within a palette.
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
