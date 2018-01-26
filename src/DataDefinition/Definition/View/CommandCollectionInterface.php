<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Interface CommandCollectionInterface.
 *
 * This interface describes a collection of commands.
 */
interface CommandCollectionInterface
{
    /**
     * Remove all commands from this collection.
     *
     * @return CommandCollectionInterface
     */
    public function clearCommands();

    /**
     * Set the commands of this collection.
     *
     * @param CommandInterface[]|array $commands The commands that shall be contained within the collection.
     *
     * @return CommandCollectionInterface
     */
    public function setCommands(array $commands);

    /**
     * Add commands to this collection.
     *
     * @param CommandInterface[]|array $commands The commands that shall be added to the collection.
     * @param CommandInterface         $before   The command before the passed commands shall be inserted (optional).
     *
     * @return CommandCollectionInterface
     */
    public function addCommands(array $commands, CommandInterface $before = null);

    /**
     * Remove commands from this collection.
     *
     * @param CommandInterface[]|array $commands The commands that shall be removed from the collection.
     *
     * @return CommandCollectionInterface
     */
    public function removeCommands(array $commands);

    /**
     * Check if the command exists in this collection.
     *
     * @param CommandInterface $command The command instance to search for.
     *
     * @return bool
     */
    public function hasCommand(CommandInterface $command);

    /**
     * Check if the command with a given name exists in this collection.
     *
     * @param string $name The command name to search.
     *
     * @return bool
     */
    public function hasCommandNamed($name);

    /**
     * Add an command to this collection.
     *
     * @param CommandInterface $command The command to add.
     * @param CommandInterface $before  The command before the passed command shall be inserted (optional).
     *
     * @return CommandCollectionInterface
     */
    public function addCommand(CommandInterface $command, CommandInterface $before = null);

    /**
     * Remove an command from this collection.
     *
     * @param CommandInterface $command The command to remove.
     *
     * @return CommandCollectionInterface
     */
    public function removeCommand(CommandInterface $command);

    /**
     * Remove an command with given name from this collection.
     *
     * @param string $name The command name to remove.
     *
     * @return CommandCollectionInterface
     */
    public function removeCommandNamed($name);

    /**
     * Get command with given name from this collection.
     *
     * @param string $name The command name to search.
     *
     * @return CommandInterface
     *
     * @throws \ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException If no command was found.
     */
    public function getCommandNamed($name);

    /**
     * Get commands from this collection.
     *
     * @return CommandInterface[]|array
     */
    public function getCommands();
}
