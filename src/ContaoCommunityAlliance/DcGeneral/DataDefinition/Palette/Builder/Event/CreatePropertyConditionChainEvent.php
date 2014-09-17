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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a property condition chain is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreatePropertyConditionChainEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-property-condition-chain';

    /**
     * The property condition chain.
     *
     * @var PropertyConditionChain
     */
    protected $conditionChain;

    /**
     * Create a new instance.
     *
     * @param PropertyConditionChain $conditionChain The property condition chain that has been created.
     *
     * @param PaletteBuilder         $paletteBuilder The palette builder in use.
     */
    public function __construct(PropertyConditionChain $conditionChain, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyConditionChain($conditionChain);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property condition chain.
     *
     * @param PropertyConditionChain $conditionChain The property condition chain.
     *
     * @return CreatePropertyConditionChainEvent
     */
    public function setPropertyConditionChain(PropertyConditionChain $conditionChain)
    {
        $this->conditionChain = $conditionChain;
        return $this;
    }

    /**
     * Retrieve the property condition chain.
     *
     * @return PropertyConditionChain
     */
    public function getPropertyConditionChain()
    {
        return $this->conditionChain;
    }
}
