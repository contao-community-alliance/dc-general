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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;

/**
 * This event gets emitted when a palette condition chain is created.
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
