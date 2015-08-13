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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;

/**
 * This event gets emitted when a palette condition chain is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreatePaletteConditionChainEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-palette-condition-chain';

    /**
     * The palette condition chain being created.
     *
     * @var PaletteConditionChain
     */
    protected $conditionChain;

    /**
     * Create a new instance.
     *
     * @param PaletteConditionChain $conditionChain The palette condition chain.
     *
     * @param PaletteBuilder        $paletteBuilder The palette builder in use.
     */
    public function __construct(PaletteConditionChain $conditionChain, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteConditionChain($conditionChain);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the palette condition chain.
     *
     * @param PaletteConditionChain $conditionChain The condition chain.
     *
     * @return CreatePaletteConditionChainEvent
     */
    public function setPaletteConditionChain(PaletteConditionChain $conditionChain)
    {
        $this->conditionChain = $conditionChain;

        return $this;
    }

    /**
     * Retrieve the palette condition chain.
     *
     * @return PaletteConditionChain
     */
    public function getPaletteConditionChain()
    {
        return $this->conditionChain;
    }
}
