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
 * Interface DataProviderInformationInterface
 *
 */
interface DataProviderInformationInterface
{
	/**
	 * Set the name of the data provider.
	 *
	 * @param $name
	 *
	 * @return DataProviderInformationInterface
	 */
	public function setName($name);

	/**
	 * Retrieve the name of the data provider.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * @param bool $versioningEnabled
	 *
	 * @return DataProviderInformationInterface
	 */
	public function setVersioningEnabled($versioningEnabled);

	/**
	 * @return bool
	 */
	public function isVersioningEnabled();
}
