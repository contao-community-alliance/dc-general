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
 * Interface CommandCollectionInterface.
 *
 * This interface describes a collection of commands.
 *
 * @package DcGeneral\DataDefinition\Definition\View
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
	 *
	 * @return CommandCollectionInterface
	 */
	public function addCommands(array $commands);

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
	 *
	 * @return CommandCollectionInterface
	 */
	public function addCommand(CommandInterface $command);

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
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException If no command was found.
	 */
	public function getCommandNamed($name);

	/**
	 * Get commands from this collection.
	 *
	 * @return CommandInterface[]|array
	 */
	public function getCommands();
}
