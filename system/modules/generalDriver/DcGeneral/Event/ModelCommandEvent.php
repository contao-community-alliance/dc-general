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
use DcGeneral\ModelAwareInterface;
use DcGeneral\Data\ModelInterface;

class ModelCommandEvent extends CommandEvent implements ModelAwareInterface
{
    const NAME = 'dc-general.model.command';
    
    /**
     * The model.
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
	 * {@inheritdoc}
	 */
	public function getModel()
	{
	    return $this->model;
	}
}
