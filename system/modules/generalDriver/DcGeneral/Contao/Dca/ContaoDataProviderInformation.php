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

namespace DcGeneral\Contao\Dca;

use DcGeneral\DataDefinition\DataProviderInformation;

class ContaoDataProviderInformation extends DataProviderInformation
{
	/**
	 * The table name to use.
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * Name of the provider class to use.
	 *
	 * @var string
	 */
	protected $className = 'DcGeneral\Data\DefaultDriver';

	/**
	 * Custom initialization data to be passed to the constructor of the driver class.
	 *
	 * @var mixed
	 */
	protected $initializationData;

	/**
	 * Set the table name of the data provider.
	 *
	 * @param $tableName
	 *
	 * @return ContaoDataProviderInformation
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Retrieve the table name of the data provider.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Set the data provider class to use, defaults to 'DcGeneral\Data\DefaultDriver'.
	 *
	 * @param string $className
	 *
	 * @return ContaoDataProviderInformation
	 */
	public function setClassName($className)
	{
		$this->className = $className;

		return $this;
	}

	/**
	 * Retrieve the data provider class to use.
	 *
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * Set the data to use for initialization of the data provider.
	 *
	 * @param mixed $initializationData
	 *
	 * @return ContaoDataProviderInformation
	 */
	public function setInitializationData($initializationData)
	{
		$this->initializationData = $initializationData;

		return $this;
	}

	/**
	 * Retrieve the data to use for initialization of the data provider.
	 *
	 * @return mixed
	 */
	public function getInitializationData()
	{
		return $this->initializationData;
	}
}
