<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;

/**
 * This event gets emitted when a palette gets finished.
 */
class FinishPaletteEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.finish-palette';

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
        $this->setPalette($palette);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the palette.
     *
     * @param PaletteInterface $palette The palette.
     *
     * @return FinishPaletteEvent
     */
    public function setPalette(PaletteInterface $palette)
    {
        $this->palette = $palette;
        return $this;
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
