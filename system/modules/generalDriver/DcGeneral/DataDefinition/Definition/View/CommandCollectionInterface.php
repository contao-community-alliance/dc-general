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
	 */
	public function clearCommands();

	/**
	 * Set the operations of this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 */
	public function setCommands(array $commands);

	/**
	 * Add operations to this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 */
	public function addCommands(array $commands);

	/**
	 * Remove operations from this collection.
	 *
	 * @param CommandInterface[]|array $commands
	 */
	public function removeCommands(array $commands);

	/**
	 * Check if the operation exists in this collection.
	 *
	 * @return bool
	 */
	public function hasCommand(CommandInterface $command);

	/**
	 * Add an operation to this collection.
	 *
	 * @param CommandInterface $command
	 */
	public function addCommand(CommandInterface $command);

	/**
	 * Remove an operation from this collection.
	 *
	 * @param CommandInterface $command
	 */
	public function removeCommand(CommandInterface $command);

	/**
	 * Get operations from this collection.
	 *
	 * @return CommandInterface[]|array
	 */
	public function getCommands();
}
