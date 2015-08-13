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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

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
