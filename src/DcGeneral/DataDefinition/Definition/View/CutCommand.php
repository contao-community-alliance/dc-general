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

namespace DcGeneral\DataDefinition\Definition\View;

/**
 * Cut command - special implementation for cutting an entry.
 *
 * Ths is merely just an empty container to tell cut commands and generic commands apart.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class CutCommand extends Command implements CutCommandInterface
{

}
