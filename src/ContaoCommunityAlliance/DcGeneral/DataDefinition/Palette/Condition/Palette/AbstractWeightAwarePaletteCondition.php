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
 * This is the abstract base class for weight aware palette conditions.
 *
 * @package DcGeneral\DataDefinition\Palette\Condition\Palette
 */
abstract class AbstractWeightAwarePaletteCondition implements WeightAwarePaletteConditionInterface
{

    /**
     * The weight of this condition.
     *
     * @var int
     */
    protected $weight = 1;

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->weight;
    }
}
