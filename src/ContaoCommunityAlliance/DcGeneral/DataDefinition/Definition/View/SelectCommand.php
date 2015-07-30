<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Select command - special implementation for selecting models for multiple actions.
 *
 * Ths is merely just an empty container to tell copy commands and generic commands apart.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class SelectCommand extends Command implements SelectCommandInterface
{
}
