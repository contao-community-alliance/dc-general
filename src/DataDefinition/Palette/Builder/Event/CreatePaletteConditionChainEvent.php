<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;

/**
 * This event gets emitted when a palette condition chain is created.
 */
class CreatePaletteConditionChainEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.create-palette-condition-chain';

    /**
     * The palette condition chain being created.
     *
     * @var ConditionChainInterface&PaletteConditionInterface
     */
    protected $conditionChain;

    /**
     * Create a new instance.
     *
     * @param ConditionChainInterface&PaletteConditionInterface $conditionChain The palette condition chain.
     * @param PaletteBuilder                                    $paletteBuilder The palette builder in use.
     */
    public function __construct(
        ConditionChainInterface&PaletteConditionInterface $conditionChain,
        PaletteBuilder $paletteBuilder
    ) {
        $this->setPaletteConditionChain($conditionChain);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the palette condition chain.
     *
     * @param ConditionChainInterface&PaletteConditionInterface $conditionChain The condition chain.
     *
     * @return CreatePaletteConditionChainEvent
     */
    public function setPaletteConditionChain(ConditionChainInterface&PaletteConditionInterface $conditionChain)
    {
        $this->conditionChain = $conditionChain;

        return $this;
    }

    /**
     * Retrieve the palette condition chain.
     *
     * @return ConditionChainInterface&PaletteConditionInterface
     */
    public function getPaletteConditionChain()
    {
        return $this->conditionChain;
    }
}
