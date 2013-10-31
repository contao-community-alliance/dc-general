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

namespace DcGeneral\Events;

use DcGeneral\EnvironmentInterface;

class CommandEvent extends BaseEvent
{
    /**
     * The command name.
     * 
     * @var string
     */
	protected $name;
	
	/**
	 * Additional command parameters.
	 * 
	 * @var array
	 */
	protected $parameters;
	
	/**
	 * Create a new command event.
	 */
	public function __construct($name, array $parameters, EnvironmentInterface $environment)
	{
	    $this->name = $name;
	    $this->parameters = $parameters;
	    $this->environment = $environment;
	}
	
	/**
	 * Return the command name.
	 * 
	 * @return string
	 */
	public function getName()
	{
	    return $this->name;
	}
	
	/**
	 * Return the additional command parameters.
	 * 
	 * @return array
	 */
	public function getParameters()
	{
	    return $this->parameters;
	}
}
