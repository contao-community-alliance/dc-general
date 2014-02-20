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

namespace DcGeneral\Contao\Compatibility;

use DcGeneral\Data\ModelInterface;
use DcGeneral\DC_General;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Class DcCompat
 *
 * Small compatibility layer for callbacks, that expect a "full featured" DC instance.
 */
class DcCompat extends DC_General
{
	/**
	 * The current model.
	 *
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * Name of the property currently working on.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Create a new instance.
	 *
	 * @param EnvironmentInterface $environment  The Dc instance to use for delegating.
	 *
	 * @param ModelInterface       $model        The model within scope.
	 *
	 * @param null                 $propertyName The name of the property within scope.
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model, $propertyName = null)
	{
		$this->objEnvironment = $environment;
		$this->model          = $model;
		$this->propertyName   = $propertyName;
	}

	/**
	 * Retrieve the current model.
	 *
	 * @return ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Retrieve the current property.
	 *
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralException This method is for internal use only.
	 */
	public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
	{
		throw new DcGeneralException(__CLASS__ . '::handlePopulateEnvironment() is internal use only and must not be called');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralException This method is for internal use only.
	 */
	protected function getTablenameCallback($strTable)
	{
		throw new DcGeneralException(__CLASS__ . '::getTablenameCallback() is internal use only and must not be called');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException The magic setter is unsupported and has been deactivated.
	 */
	public function __set($strKey, $varValue)
	{
		throw new DcGeneralRuntimeException('The magic setter is not supported anymore!');
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'id':
				return $this->model->getId();

			case 'parentTable':
				throw new DcGeneralRuntimeException('The magic property $dc->parentTable is not supported yet!');

			case 'childTable':
				throw new DcGeneralRuntimeException('The magic property $dc->childTable is not supported yet!');

			case 'rootIds':
				throw new DcGeneralRuntimeException('The magic property $dc->rootIds is not supported yet!');

			case 'createNewVersion':
				throw new DcGeneralRuntimeException('The magic property $dc->createNewVersion is not supported yet!');

			case 'table':
				throw new DcGeneralRuntimeException('The magic property $dc->table is not supported yet!');

			case 'value':
				if ($this->propertyName) {

				}
				return null;

			case 'field':
				return $this->propertyName;

			case 'inputName':
				throw new DcGeneralRuntimeException('The magic property $dc->inputName is not supported yet!');

			case 'palette':
				throw new DcGeneralRuntimeException('The magic property $dc->palette is not supported yet!');

			case 'activeRecord':
				return new ActiveRecord($this->model);

			default:
		}

		throw new DcGeneralRuntimeException('The magic property ' . $name . ' is not supported (yet)!');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDCA()
	{
		// NOTE: This is the only part from legacy DC_General we can not retrieve via Environment.
		// It is usually passed via constructor call in DC_General but in 99.9% of all cases, this is the direct
		// mapping of the globals DCA.
		return $GLOBALS['TL_DCA'][$this->getEnvironment()->getParentDataDefinition()->getName()];
	}
}
