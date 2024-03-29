<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
     * @param ModelInterface|null    $model    If given, subpalettes will be evaluated depending on the model.
     *                                         If no model is given, all properties will be returned, including
     *                                         subpalette properties.
     * @param PropertyValueBag|null  $input    If given, subpalettes will be evaluated depending on the input data.
     *                                         If no model and no input data is given, all properties will be returned,
     *                                         including subpalette properties.
     * @param PropertyInterface|null $property The defined property.
     * @param LegendInterface|null   $legend   The legend the property is assigned to.
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
