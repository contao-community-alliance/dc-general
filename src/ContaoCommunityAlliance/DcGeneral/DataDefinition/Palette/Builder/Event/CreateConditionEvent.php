<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition
    as PalettePropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event gets emitted when a condition is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreateConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-condition';

    /**
     * The condition being created.
     *
     * @var PaletteConditionInterface|PropertyConditionInterface
     */
    protected $condition;

    /**
     * Create a new instance.
     *
     * @param PaletteConditionInterface|PropertyConditionInterface $condition      The condition that has been created.
     *
     * @param PaletteBuilder                                       $paletteBuilder The palette builder that created the
     *                                                                             condition.
     */
    public function __construct($condition, PaletteBuilder $paletteBuilder)
    {
        $this->setCondition($condition);

        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param PaletteConditionInterface|PropertyConditionInterface $condition The condition to use.
     *
     * @return CreateConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid condition has been passed.
     */
    public function setCondition($condition)
    {
        if ((!$condition instanceof PaletteConditionInterface) && (!$condition instanceof PropertyConditionInterface))
        {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->condition = $condition;

        return $this;
    }

    /**
     * Retrieve the condition.
     *
     * @return PalettePropertyValueCondition|PropertyValueCondition
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
