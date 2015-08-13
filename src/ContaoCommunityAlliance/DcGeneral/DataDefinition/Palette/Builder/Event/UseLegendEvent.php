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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;

/**
 * This event gets emitted when a legend is used.
 */
class UseLegendEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-legend';

    /**
     * The legend interface.
     *
     * @var LegendInterface
     */
    protected $legend;

    /**
     * Create a new instance.
     *
     * @param LegendInterface $legend         The legend being used.
     *
     * @param PaletteBuilder  $paletteBuilder The palette builder in use.
     */
    public function __construct(LegendInterface $legend, PaletteBuilder $paletteBuilder)
    {
        $this->legend = $legend;
        parent::__construct($paletteBuilder);
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
