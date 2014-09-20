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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition
    as PalettePropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event gets emitted when a property value condition gets created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreatePropertyValueConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-property-value-condition';

    /**
     * The property value condition.
     *
     * @var PalettePropertyValueCondition|PropertyValueCondition
     */
    protected $condition;

    /**
     * Create a new instance.
     *
     * @param PalettePropertyValueCondition|PropertyValueCondition $condition      The condition.
     *
     * @param PaletteBuilder                                       $paletteBuilder The palette builder in use.
     */
    public function __construct($condition, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyValueCondition($condition);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property value condition.
     *
     * @param PalettePropertyValueCondition|PropertyValueCondition $condition The property value condition.
     *
     * @return CreatePropertyValueConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid condition has been passed.
     */
    public function setPropertyValueCondition($condition)
    {
        if (!($condition instanceof PalettePropertyValueCondition)
            && (!$condition instanceof PropertyValueCondition)
        ) {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->condition = $condition;
        return $this;
    }

    /**
     * Retrieve the property value condition.
     *
     * @return PalettePropertyValueCondition|PropertyValueCondition
     */
    public function getPropertyValueCondition()
    {
        return $this->condition;
    }
}
