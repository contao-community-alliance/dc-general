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
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;

/**
 * This event is the base class for all palette builder events.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
abstract class BuilderEvent extends AbstractContainerAwareEvent
{

    /**
     * The palette builder in use.
     *
     * @var PaletteBuilder
     */
    protected $paletteBuilder;

    /**
     * Create a new instance.
     *
     * @param PaletteBuilder $paletteBuilder The palette builder in use.
     */
    public function __construct(PaletteBuilder $paletteBuilder)
    {
        parent::__construct($this->paletteBuilder->getContainer());

        $this->paletteBuilder = $paletteBuilder;
    }

    /**
     * Retrieve the palette builder.
     *
     * @return PaletteBuilder
     */
    public function getPaletteBuilder()
    {
        return $this->paletteBuilder;
    }
}
