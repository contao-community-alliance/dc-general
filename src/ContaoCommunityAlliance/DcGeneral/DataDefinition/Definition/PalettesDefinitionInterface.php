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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

/**
 * This interface describes a palette definition.
 *
 * All methods are being derived from the parenting interfaces currently.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface PalettesDefinitionInterface extends DefinitionInterface, PaletteCollectionInterface
{
    /**
     * The name of the definition.
     */
    const NAME = 'palettes';
}
