<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;

/**
 * This event is the base class for all palette builder events.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class BuilderEvent extends AbstractContainerAwareEvent
{
    /**
     * @var string
     */
    public const NAME = 'dc-general.data-definition.palette.builder.builder';

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
        parent::__construct($paletteBuilder->getContainer());

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
