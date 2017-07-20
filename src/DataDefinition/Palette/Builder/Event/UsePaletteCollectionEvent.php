<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

/**
 * This event gets emitted when a palette collection gets used.
 */
class UsePaletteCollectionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-palette-collection';

    /**
     * The palette collection.
     *
     * @var PaletteCollectionInterface
     */
    protected $paletteCollection;

    /**
     * Create a new instance.
     *
     * @param PaletteCollectionInterface $paletteCollection The palette collection.
     *
     * @param PaletteBuilder             $paletteBuilder    The palette builder in use.
     */
    public function __construct(PaletteCollectionInterface $paletteCollection, PaletteBuilder $paletteBuilder)
    {
        $this->paletteCollection = $paletteCollection;
        parent::__construct($paletteBuilder);
    }

    /**
     * Retrieve the palette collection.
     *
     * @return PaletteCollectionInterface
     */
    public function getPaletteCollection()
    {
        return $this->paletteCollection;
    }
}
