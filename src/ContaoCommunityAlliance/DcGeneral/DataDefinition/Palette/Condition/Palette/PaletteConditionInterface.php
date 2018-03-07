<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * A condition define when a palette is used or not.
 */
interface PaletteConditionInterface extends ConditionInterface
{
    /**
     * Calculate how "strong" (aka "count of matches") this condition match the model and input parameters.
     *
     * If a value is present in the input parameter, that one overrides any existing value in the model.
     *
     * When the condition does not match at all or has not enough information for a decision, false must be returned.
     *
     * When the condition does match, it must return a numeric value, the value may be negative or positive and even
     * zero.
     *
     * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
     * @param PropertyValueBag    $input If given, selectors will be evaluated depending on the input data.
     *
     * @return bool|int
     */
    public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null);

    /**
     * Create a deep clone of the condition.
     *
     * @return void
     */
    public function __clone();
}
