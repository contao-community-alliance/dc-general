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

interface CommandCollectionInterface
{
	/**
	 * Remove all operations from this collection.
	 *
	 * @return CommandCollectionInterface
	 */
	public function clearCommands();

	/**
	 * Set the operations of this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 *
	 * @return CommandCollectionInterface
	 */
	public function setCommands(array $commands);

	/**
	 * Add operations to this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 *
	 * @return CommandCollectionInterface
	 */
	public function addCommands(array $commands);

	/**
	 * Remove operations from this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 *
	 * @return CommandCollectionInterface
	 */
	public function removeCommands(array $commands);

	/**
	 * Check if the operation exists in this collection.
	 *
	 * @param CommandInterface $command
	 *
	 * @return bool
	 */
	public function hasCommand(CommandInterface $command);

	/**
	 * Check if the operation with a given name exists in this collection.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasCommandNamed($name);

	/**
	 * Add an operation to this collection.
	 *
	 * @param CommandInterface $command
	 *
	 * @return CommandCollectionInterface
	 */
	public function addCommand(CommandInterface $command);

	/**
	 * Remove an operation from this collection.
	 *
	 * @param CommandInterface $command
	 *
	 * @return CommandCollectionInterface
	 */
	public function removeCommand(CommandInterface $command);

	/**
	 * Remove an operation with given name from this collection.
	 *
	 * @param string $name
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException If no command was found.
	 */
	public function removeCommandNamed($name);

	/**
	 * Get operation with given name from this collection.
	 *
	 * @param string $name
	 *
	 * @return CommandInterface
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException If no command was found.
	 */
	public function getCommandNamed($name);

	/**
	 * Get operations from this collection.
	 *
	 * @return CommandInterface[]|array
	 */
	public function getCommands();
}
