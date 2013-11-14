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
use DcGeneral\EnvironmentInterface;
use DcGeneral\ModelAwareInterface;

class AbstractModelAwareEvent
	extends AbstractEnvironmentAwareEvent
	implements ModelAwareInterface
{
	/**
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * Create a new model aware event.
	 * 
	 * @param ModelInterface $model
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model)
	{
		parent::__construct($environment);
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModel()
	{
		return $this->model;
	}
}
