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
use DcGeneral\EnvironmentAwareInterface;
use DcGeneral\EnvironmentInterface;

interface CommandEventInterface extends EnvironmentAwareInterface
{
	/**
	 * Return the command.
	 * 
	 * @return CommandInterface
	 */
	public function getCommand();
}
