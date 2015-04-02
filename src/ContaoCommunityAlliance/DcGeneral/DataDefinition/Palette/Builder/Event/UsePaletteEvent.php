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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;

/**
 * This event gets emitted when a palette is used.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class UsePaletteEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-palette';

    /**
     * The palette.
     *
     * @var PaletteInterface
     */
    protected $palette;

    /**
     * Create a new instance.
     *
     * @param PaletteInterface $palette        The palette.
     *
     * @param PaletteBuilder   $paletteBuilder The palette builder in use.
     */
    public function __construct(PaletteInterface $palette, PaletteBuilder $paletteBuilder)
    {
        $this->palette = $palette;
        parent::__construct($paletteBuilder);
    }

    /**
     * Retrieve the palette.
     *
     * @return PaletteInterface
     */
    public function getPalette()
    {
        return $this->palette;
    }
}
