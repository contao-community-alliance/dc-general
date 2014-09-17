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
 * This event gets emitted when a legend class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetLegendClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-legend-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $legendClassName;

    /**
     * Create a new instance.
     *
     * @param string         $legendClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder  The palette builder in use.
     */
    public function __construct($legendClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setLegendClassName($legendClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the legend class name.
     *
     * @param string $legendClassName The class name.
     *
     * @return SetLegendClassNameEvent
     */
    public function setLegendClassName($legendClassName)
    {
        $this->legendClassName = (string)$legendClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getLegendClassName()
    {
        return $this->legendClassName;
    }
}
