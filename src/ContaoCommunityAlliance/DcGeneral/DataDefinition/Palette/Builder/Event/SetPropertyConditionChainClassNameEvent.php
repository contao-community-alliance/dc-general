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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a property condition chain class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPropertyConditionChainClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-property-condition-chain-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $palettePropertyConditionChainClassName;

    /**
     * Create a new instance.
     *
     * @param string         $palettePropertyConditionChainClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder                         The palette builder in use.
     */
    public function __construct($palettePropertyConditionChainClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPalettePropertyConditionChainClassName($palettePropertyConditionChainClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $palettePropertyConditionChainClassName The class name.
     *
     * @return SetPropertyConditionChainClassNameEvent
     */
    public function setPalettePropertyConditionChainClassName($palettePropertyConditionChainClassName)
    {
        $this->palettePropertyConditionChainClassName = (string)$palettePropertyConditionChainClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPalettePropertyConditionChainClassName()
    {
        return $this->palettePropertyConditionChainClassName;
    }
}
