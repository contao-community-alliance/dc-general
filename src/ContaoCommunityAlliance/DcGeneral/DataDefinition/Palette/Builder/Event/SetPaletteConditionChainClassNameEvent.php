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

/**
 * This event gets emitted when a palette condition chain class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPaletteConditionChainClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-palette-condition-chain-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $paletteConditionChainClassName;

    /**
     * Create a new instance.
     *
     * @param string         $paletteConditionChainClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder                 The palette builder in use.
     */
    public function __construct($paletteConditionChainClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteConditionChainClassName($paletteConditionChainClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $paletteConditionChainClassName The class name.
     *
     * @return SetPaletteConditionChainClassNameEvent
     */
    public function setPaletteConditionChainClassName($paletteConditionChainClassName)
    {
        $this->paletteConditionChainClassName = (string)$paletteConditionChainClassName;
        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPaletteConditionChainClassName()
    {
        return $this->paletteConditionChainClassName;
    }
}
