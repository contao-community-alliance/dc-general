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

use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Definition\View\CommandInterface;
use DcGeneral\EnvironmentInterface;

/**
 * Abstract base class for a command event referencing a model.
 *
 * @package DcGeneral\Event
 */
abstract class AbstractModelCommandEvent
	extends AbstractCommandEvent
	implements ModelCommandEventInterface
{
	/**
	 * The attached model.
	 *
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * Create a new instance.
	 *
	 * @param CommandInterface     $command     The command.
	 *
	 * @param ModelInterface       $model       The model.
	 *
	 * @param EnvironmentInterface $environment The environment.
	 */
	public function __construct(CommandInterface $command, ModelInterface $model, EnvironmentInterface $environment)
	{
		parent::__construct($command, $environment);
		$this->model = $model;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModel()
	{
		return $this->model;
	}
}
