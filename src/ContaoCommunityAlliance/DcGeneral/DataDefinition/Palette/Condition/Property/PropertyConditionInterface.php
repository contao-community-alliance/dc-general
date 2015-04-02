<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * A condition define when a property is visible or editable and when not.
 */
interface PropertyConditionInterface extends ConditionInterface
{
    /**
     * Check if the condition match.
     *
     * @param ModelInterface|null $model    If given, subpalettes will be evaluated depending on the model.
     *                                      If no model is given, all properties will be returned, including subpalette
     *                                      properties.
     *
     * @param PropertyValueBag    $input    If given, subpalettes will be evaluated depending on the input data.
     *                                      If no model and no input data is given, all properties will be returned,
     *                                      including subpalette properties.
     *
     * @param PropertyInterface   $property The defined property.
     *
     * @param LegendInterface     $legend   The legend the property is assigned to.
     *
     * @return bool
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    );

    /**
     * Create a deep clone of the condition.
     *
     * @return void
     */
    public function __clone();
}
