<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Negate a condition.
 */
class NotCondition implements PropertyConditionInterface
{
    /**
     * The condition to negate.
     *
     * @var PropertyConditionInterface
     */
    protected $condition;

    /**
     * Create a new instance.
     *
     * @param PropertyConditionInterface $condition The condition to negate.
     */
    public function __construct(PropertyConditionInterface $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Set the condition to negate.
     *
     * @param PropertyConditionInterface $condition The condition.
     *
     * @return NotCondition
     */
    public function setCondition(PropertyConditionInterface $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Retrieve the condition to negate.
     *
     * @return PropertyConditionInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        return !$this->condition->match($model, $input, $property, $legend);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->condition = clone $this->condition;
    }
}
