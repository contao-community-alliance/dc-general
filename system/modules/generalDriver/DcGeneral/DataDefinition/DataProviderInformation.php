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

namespace DcGeneral\DataDefinition;

/**
 * Interface DataProviderInformation
 *
 */
class DataProviderInformation implements DataProviderInformationInterface
{
	protected $name;

	protected $versioningEnabled;

	/**
	 * Set the name of the data provider.
	 *
	 * @param $name
	 *
	 * @return DataProviderInformationInterface
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Retrieve the name of the data provider.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param bool $versioningEnabled
	 *
	 * @return DataProviderInformationInterface
	 */
	public function setVersioningEnabled($versioningEnabled)
	{
		$this->versioningEnabled = $versioningEnabled;
	}

	/**
	 * @return bool
	 */
	public function isVersioningEnabled()
	{
		return $this->versioningEnabled;
	}
}
