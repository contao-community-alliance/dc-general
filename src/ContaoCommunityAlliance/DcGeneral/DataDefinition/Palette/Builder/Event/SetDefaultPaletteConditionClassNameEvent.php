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
 * This event gets emitted when the class name of the default palette condition is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetDefaultPaletteConditionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-default-palette-condition-class-name';

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
        $this->setDefaultPaletteConditionClassName($className);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $className The class name.
     *
     * @return SetDefaultPaletteConditionClassNameEvent
     */
    public function setDefaultPaletteConditionClassName($className)
    {
        $this->className = (string) $className;
        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getDefaultPaletteConditionClassName()
    {
        return $this->className;
    }
}
