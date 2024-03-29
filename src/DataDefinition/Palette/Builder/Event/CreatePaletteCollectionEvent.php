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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

/**
 * This event gets emitted when a palette collection is being created.
 */
class CreatePaletteCollectionEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.create-palette-collection';

    /**
     * The palette collection that has been created.
     *
     * @var PaletteCollectionInterface
     */
    protected $paletteCollection;

    /**
     * Create a new instance.
     *
     * @param PaletteCollectionInterface $paletteCollection The palette collection that has been created.
     * @param PaletteBuilder             $paletteBuilder    The palette builder in use.
     */
    public function __construct(PaletteCollectionInterface $paletteCollection, PaletteBuilder $paletteBuilder)
    {
        $this->setPaletteCollection($paletteCollection);

        parent::__construct($paletteBuilder);
    }

    /**
     * Set the palette collection.
     *
     * @param PaletteCollectionInterface $paletteCollection The palette collection.
     *
     * @return CreatePaletteCollectionEvent
     */
    public function setPaletteCollection(PaletteCollectionInterface $paletteCollection)
    {
        $this->paletteCollection = $paletteCollection;

        return $this;
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
