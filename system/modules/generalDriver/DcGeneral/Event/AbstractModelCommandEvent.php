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
use DcGeneral\ModelAwareInterface;

abstract class AbstractModelCommandEvent extends AbstractCommandEvent implements ModelCommandEventInterface
{
	/**
	 * @var ModelInterface
	 */
	protected $model;

	function __construct(CommandInterface $command, ModelInterface $model, EnvironmentInterface $environment)
	{
		parent::__construct($command, $environment);
		$this->model = $model;
	}

	/**
	 * @return
	 */
	public function getModel()
	{
	    return $this->model;
	}
}
