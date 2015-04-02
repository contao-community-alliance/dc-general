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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event is emitted when an condition is added by the palette builder.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class AddConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.add-condition';

    /**
     * The condition that is being added.
     *
     * @var PaletteConditionInterface|PropertyConditionInterface
     */
    protected $condition;

    /**
     * The target to which the condition is being added.
     *
     * @var PaletteInterface|PropertyInterface
     */
    protected $target;

    /**
     * Create a new instance.
     *
     * @param PaletteConditionInterface|PropertyConditionInterface $condition      The condition being added.
     *
     * @param PaletteInterface|PropertyInterface                   $target         The target property or palette.
     *
     * @param PaletteBuilder                                       $paletteBuilder The palette builder in use.
     */
    public function __construct($condition, $target, PaletteBuilder $paletteBuilder)
    {
        $this->setCondition($condition);
        $this->setTarget($target);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param PaletteConditionInterface|PropertyConditionInterface $condition The condition.
     *
     * @return AddConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid condition has been passed.
     */
    public function setCondition($condition)
    {
        if ((!$condition instanceof PaletteConditionInterface) && (!$condition instanceof PropertyConditionInterface)) {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->condition = $condition;
        return $this;
    }

    /**
     * Retrieve the condition.
     *
     * @return PaletteConditionInterface|PropertyConditionInterface
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set the target.
     *
     * @param PaletteInterface|PropertyInterface $target The target property or palette.
     *
     * @return AddConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid target has been passed.
     */
    public function setTarget($target)
    {
        if ((!$target instanceof PaletteInterface) && (!$target instanceof PropertyInterface)) {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->target = $target;
        return $this;
    }

    /**
     * Retrieve the target.
     *
     * @return PaletteInterface|PropertyInterface
     */
    public function getTarget()
    {
        return $this->target;
    }
}
