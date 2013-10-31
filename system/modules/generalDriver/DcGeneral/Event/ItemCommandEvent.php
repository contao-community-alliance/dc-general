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

use DcGeneral\EnvironmentInterface;
use DcGeneral\Data\ModelInterface;

class ItemCommandEvent extends CommandEvent
{
    const NAME = 'dc-general.item.command';
    
    /**
     * The item.
     * 
     * @var ModelInterface
     */
	protected $item;
	
	/**
	 * Create a new command event.
	 */
	public function __construct(ModelInterface $item, $name, array $parameters, EnvironmentInterface $environment)
	{
	    $this->item = $item;
	    parent::__construct($name, $parameters, $environment);
	}
	
	/**
	 * Return the item.
	 * 
	 * @return ModelInterface
	 */
	public function getItem()
	{
	    return $this->item;
	}
}
