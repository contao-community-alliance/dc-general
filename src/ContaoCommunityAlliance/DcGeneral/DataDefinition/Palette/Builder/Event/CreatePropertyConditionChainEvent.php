<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;

/**
 * This event gets emitted when a property condition chain is created.
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
