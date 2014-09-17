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

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class is the base foundation for a command event.
 *
 * @package DcGeneral\Event
 */
abstract class AbstractCommandEvent extends AbstractEnvironmentAwareEvent implements CommandEventInterface
{
    /**
     * The command attached to the event.
     *
     * @var CommandInterface
     */
    protected $command;

    /**
     * Create a new instance.
     *
     * @param CommandInterface     $command     The command to attach.
     *
     * @param EnvironmentInterface $environment The environment in use.
     */
    public function __construct(CommandInterface $command, EnvironmentInterface $environment)
    {
        parent::__construct($environment);
        $this->command = $command;
    }

    /**
     * Return the command.
     *
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }
}
