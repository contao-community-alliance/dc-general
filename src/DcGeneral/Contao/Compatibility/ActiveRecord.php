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

namespace DcGeneral\Contao\Compatibility;

use DcGeneral\Data\ModelInterface;
use DcGeneral\DC_General;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Class ActiveRecord
 *
 * Small compatibility layer for the $dc->activeRecord property.
 */
class ActiveRecord
{
	/**
	 * The underlying model.
	 *
	 * @var ModelInterface
	 */
	protected $model;

	public function __construct(ModelInterface $model)
	{
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 */
	function __get($name)
	{
		return $this->model->getProperty($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __set($name, $value)
	{
		$this->model->setProperty($name, $value);
	}

	/**
	 * Return the underlying model.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}
}
