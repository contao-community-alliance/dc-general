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

namespace DcGeneral\DataDefinition\Definition;

use DcGeneral\DataDefinition\DataProviderInformation;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Interface DataProviderDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface DataProviderDefinitionInterface
	extends DefinitionInterface,
	\IteratorAggregate,
	\Countable,
	\ArrayAccess
{
	const NAME = 'dataProvider';

	/**
	 * @param DataProviderInformation|string $information
	 *
	 * @throws DcGeneralInvalidArgumentException
	 *
	 * @return DataProviderDefinitionInterface
	 */
	public function addInformation($information);

	/**
	 * @param $information
	 *
	 * @return DataProviderDefinitionInterface
	 */
	public function removeInformation($information);

	/**
	 * @param string                  $name
	 *
	 * @param DataProviderInformation $information
	 *
	 * @return DataProviderDefinitionInterface
	 */
	public function setInformation($name, $information);

	/**
	 * @param DataProviderInformation|string $information
	 *
	 * @throws DcGeneralInvalidArgumentException
	 *
	 * @return bool
	 */
	public function hasInformation($information);

	/**
	 * @param string $information
	 *
	 * @return DataProviderInformation
	 */
	public function getInformation($information);

	/**
	 * Retrieve the names of all registered providers.
	 *
	 * @return string[]
	 */
	public function getProviderNames();
}
