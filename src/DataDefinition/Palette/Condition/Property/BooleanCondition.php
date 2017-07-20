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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * Condition for specifying an explicit boolean value (Useful for determining if a property shall be editable i.e.).
 */
class BooleanCondition implements PropertyConditionInterface
{
    /**
     * The boolean value to return.
     *
     * @var bool
     */
    protected $value;

    /**
     * Create a new instance.
     *
     * @param bool $value The value to use.
     */
    public function __construct($value)
    {
        $this->value = (bool) $value;
    }

    /**
     * Set the value.
     *
     * @param bool $value The value to use.
     *
     * @return BooleanCondition
     */
    public function setValue($value)
    {
        $this->value = (bool) $value;

        return $this;
    }

    /**
     * Retrieve the value.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getValue()
    {
        return $this->value;
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
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
