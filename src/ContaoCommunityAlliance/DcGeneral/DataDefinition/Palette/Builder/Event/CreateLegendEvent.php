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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;

/**
 * This event gets emitted when a legend is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreateLegendEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-legend';

    /**
     * The legend that has been created.
     *
     * @var LegendInterface
     */
    protected $legend;

    /**
     * Create a new instance.
     *
     * @param LegendInterface $legend         The legend being created.
     *
     * @param PaletteBuilder  $paletteBuilder The palette builder in use.
     */
    public function __construct(LegendInterface $legend, PaletteBuilder $paletteBuilder)
    {
        $this->setLegend($legend);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the legend.
     *
     * @param LegendInterface $legend The legend.
     *
     * @return CreateLegendEvent
     */
    public function setLegend(LegendInterface $legend)
    {
        $this->legend = $legend;

        return $this;
    }

    /**
     * Retrieve the legend.
     *
     * @return LegendInterface
     */
    public function getLegend()
    {
        return $this->legend;
    }
}
