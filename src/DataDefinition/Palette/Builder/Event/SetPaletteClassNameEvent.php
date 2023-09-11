<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;

/**
 * This event gets emitted when a palette class name is set.
 */
class SetPaletteClassNameEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.set-palette-class-name';

    /**
     * The palette class name.
     *
     * @var class-string<PaletteInterface>
     */
    protected $paletteClassName;

    /**
     * Create a new instance.
     *
     * @param class-string<PaletteInterface> $paletteClassName The class name.
     * @param PaletteBuilder                 $paletteBuilder   The palette builder in use.
     */
    public function __construct($paletteClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteClassName($paletteClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param class-string<PaletteInterface> $paletteClassName The class name.
     *
     * @return SetPaletteClassNameEvent
     */
    public function setPaletteClassName($paletteClassName)
    {
        $this->paletteClassName = $paletteClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return class-string<PaletteInterface>
     */
    public function getPaletteClassName()
    {
        return $this->paletteClassName;
    }
}
