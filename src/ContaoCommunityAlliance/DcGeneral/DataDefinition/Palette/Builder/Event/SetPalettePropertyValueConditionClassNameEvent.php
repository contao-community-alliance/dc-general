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

/**
 * This event gets emitted when a palette property value condition class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPalettePropertyValueConditionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-palette-property-value-condition-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $className;

    /**
     * Create a new instance.
     *
     * @param string         $className      The class name.
     *
     * @param PaletteBuilder $paletteBuilder The palette builder in use.
     */
    public function __construct($className, PaletteBuilder $paletteBuilder)
    {
        $this->setPalettePropertyValueConditionClassName($className);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $className The class name.
     *
     * @return SetPalettePropertyValueConditionClassNameEvent
     */
    public function setPalettePropertyValueConditionClassName($className)
    {
        $this->className = (string) $className;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPalettePropertyValueConditionClassName()
    {
        return $this->className;
    }
}
