<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Factory;

interface DcGeneralFactoryInterface
{
	/**
	 * @param string $environmentClassName
	 */
	public function setEnvironmentClassName($environmentClassName);

	/**
	 * @return string
	 */
	public function getEnvironmentClassName();

	/**
	 * @param string $containerName
	 */
	public function setContainerName($containerName);

	/**
	 * @return string
	 */
	public function getContainerName();

	/**
	 * @param string $containerClassName
	 */
	public function setContainerClassName($containerClassName);

	/**
	 * @return string
	 */
	public function getContainerClassName();

	/**
	 * @param string $dcGeneralClassName
	 */
	public function setDcGeneralClassName($dcGeneralClassName);

	/**
	 * @return string
	 */
	public function getDcGeneralClassName();

	/**
	 * @return mixed
	 */
	public function createDcGeneral();
}
