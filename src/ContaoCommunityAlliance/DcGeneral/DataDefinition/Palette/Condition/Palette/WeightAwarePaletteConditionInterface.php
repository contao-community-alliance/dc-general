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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

/**
 * This interface describes a weight aware palette condition.
 *
 * @package DcGeneral\DataDefinition\Palette\Condition\Palette
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
