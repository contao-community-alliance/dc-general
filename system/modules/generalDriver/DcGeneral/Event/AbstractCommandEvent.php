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

namespace DcGeneral\Event;

use DcGeneral\DataDefinition\Definition\View\CommandInterface;
use DcGeneral\EnvironmentInterface;

abstract class AbstractCommandEvent extends AbstractEnvironmentAwareEvent implements CommandEventInterface
{
	/**
	 * @var CommandInterface
	 */
	protected $command;

	function __construct(CommandInterface $command, EnvironmentInterface $environment)
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
