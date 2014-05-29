<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Copy command - special implementation for coping an entry.
 *
 * Ths is merely just an empty container to tell copy commands and generic commands apart.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class CopyCommand extends Command implements CopyCommandInterface
{
}
