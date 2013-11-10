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

class CommandCollection implements CommandCollectionInterface
{
	/**
	 * @var array
	 */
	protected $commands = array();

	/**
	 * {@inheritdoc}
	 */
	public function clearCommands()
	{
		$this->commands = array();
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
		foreach ($commands as $command) {
			$this->addCommand($command);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeCommands(array $commands)
	{
		foreach ($commands as $command) {
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
	 */
	public function getCommands()
	{
		return $this->commands;
	}
}
