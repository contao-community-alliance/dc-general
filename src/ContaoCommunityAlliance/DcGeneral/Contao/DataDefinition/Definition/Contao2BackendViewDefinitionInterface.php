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

namespace ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ViewDefinitionInterface;

/**
 * Interface BasicDefinitionInterface.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface Contao2BackendViewDefinitionInterface extends ViewDefinitionInterface
{
    /**
     * The name of the definition.
     */
    const NAME = 'view.contao2backend';
}
