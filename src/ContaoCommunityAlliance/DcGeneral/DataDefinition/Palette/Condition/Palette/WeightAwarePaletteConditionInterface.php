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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

/**
 * This interface describes a weight aware palette condition.
 */
interface WeightAwarePaletteConditionInterface extends PaletteConditionInterface
{
    /**
     * Set the weight of this condition.
     *
     * @param int $weight The weight of this condition.
     *
     * @return WeightAwarePaletteConditionInterface
     */
    public function setWeight($weight);

    /**
     * Get the weight of this condition.
     *
     * @return int
     */
    public function getWeight();
}
