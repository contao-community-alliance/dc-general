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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * This event gets emitted when a property condition chain is created.
 */
class CreatePropertyConditionChainEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.create-property-condition-chain';

    /**
     * The property condition chain.
     *
     * @var ConditionChainInterface&PropertyConditionInterface
     */
    protected $conditionChain;

    /**
     * Create a new instance.
     *
     * @param ConditionChainInterface&PropertyConditionInterface $conditionChain The property condition chain that has
     *                                                                           been created.
     * @param PaletteBuilder                                     $paletteBuilder The palette builder in use.
     */
    public function __construct(
        ConditionChainInterface&PropertyConditionInterface $conditionChain,
        PaletteBuilder $paletteBuilder
    ) {
        $this->setPropertyConditionChain($conditionChain);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property condition chain.
     *
     * @param ConditionChainInterface&PropertyConditionInterface $conditionChain The property condition chain.
     *
     * @return CreatePropertyConditionChainEvent
     */
    public function setPropertyConditionChain(ConditionChainInterface&PropertyConditionInterface $conditionChain)
    {
        $this->conditionChain = $conditionChain;
        return $this;
    }

    /**
     * Retrieve the property condition chain.
     *
     * @return ConditionChainInterface&PropertyConditionInterface
     */
    public function getPropertyConditionChain()
    {
        return $this->conditionChain;
    }
}
