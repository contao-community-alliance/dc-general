<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;

/**
 * This interface describes an event referencing an environment and a command.
 *
 * @package DcGeneral\Event
 */
interface CommandEventInterface extends EnvironmentAwareInterface
{
    /**
     * Return the command.
     *
     * @return CommandInterface
     */
    public function getCommand();
}
