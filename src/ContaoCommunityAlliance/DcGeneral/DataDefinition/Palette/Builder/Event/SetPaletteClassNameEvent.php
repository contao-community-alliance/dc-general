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
 * This event gets emitted when a palette class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPaletteClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-palette-class-name';

    /**
     * The palette class name.
     *
     * @var string
     */
    protected $paletteClassName;

    /**
     * Create a new instance.
     *
     * @param string         $paletteClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder   The palette builder in use.
     */
    public function __construct($paletteClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteClassName($paletteClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $paletteClassName The class name.
     *
     * @return SetPaletteClassNameEvent
     */
    public function setPaletteClassName($paletteClassName)
    {
        $this->paletteClassName = (string) $paletteClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPaletteClassName()
    {
        return $this->paletteClassName;
    }
}
