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

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class CommandCollection.
 *
 * Implementation of a command collection.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class CommandCollection implements CommandCollectionInterface
{
	/**
	 * The commands contained within the collection.
	 *
	 * @var CommandInterface[]
	 */
	protected $commands = array();

	/**
	 * {@inheritdoc}
	 */
	public function clearCommands()
	{
		$this->commands = array();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCommands(array $commands)
	{
		$this->clearCommands();
		$this->addCommands($commands);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addCommands(array $commands)
	{
		foreach ($commands as $command)
		{
			$this->addCommand($command);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeCommands(array $commands)
	{
		foreach ($commands as $command)
		{
			$this->removeCommand($command);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasCommand(CommandInterface $command)
	{
		$hash = spl_object_hash($command);

		return isset($this->commands[$hash]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasCommandNamed($name)
	{
		foreach ($this->commands as $command)
		{
			if ($command->getName() == $name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addCommand(CommandInterface $command)
	{
		$hash = spl_object_hash($command);

		$this->commands[$hash] = $command;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeCommand(CommandInterface $command)
	{
		$hash = spl_object_hash($command);
		unset($this->commands[$hash]);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException when the requested command could not be found.
	 */
	public function removeCommandNamed($name)
	{
		foreach ($this->commands as $command)
		{
			if ($command->getName() == $name)
			{
				$this->removeCommand($command);

				return $this;
			}
		}

		throw new DcGeneralInvalidArgumentException('Command with name ' . $name . ' not found');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException when the requested command could not be found.
	 */
	public function getCommandNamed($name)
	{
		foreach ($this->commands as $command)
		{
			if ($command->getName() == $name)
			{
				return $command;
			}
		}

		throw new DcGeneralInvalidArgumentException('Command with name ' . $name . ' not found');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCommands()
	{
		return $this->commands;
	}
}
