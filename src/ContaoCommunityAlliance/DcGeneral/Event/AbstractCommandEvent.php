<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class is the base foundation for a command event.
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
