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
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a palette collection class name is set.
 */
class SetPaletteCollectionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-palette-collection-class-name';

    /**
     * The palette collection class name.
     *
     * @var string
     */
    protected $className;

    /**
     * Create a new instance.
     *
     * @param string         $className      The class name.
     *
     * @param PaletteBuilder $paletteBuilder The palette builder.
     */
    public function __construct($className, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteCollectionClassName($className);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $className The class name.
     *
     * @return SetPaletteCollectionClassNameEvent
     */
    public function setPaletteCollectionClassName($className)
    {
        $this->className = (string) $className;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPaletteCollectionClassName()
    {
        return $this->className;
    }
}
