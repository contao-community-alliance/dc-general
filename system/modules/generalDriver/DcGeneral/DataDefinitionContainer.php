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

namespace DcGeneral;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class DataDefinitionContainer implements DataDefinitionContainerInterface
{
	/**
	 * @var ContainerInterface[]
	 */
	protected $definitions;

	/**
	 * {@inheritDoc}
	 */
	public function setDefinition($name, $definition)
	{
		if ($definition)
		{
			$this->definitions[$name] = $definition;
		}
		else
		{
			unset($this->definitions[$name]);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasDefinition($name)
	{
		return isset($this->definitions[$name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefinition($name)
	{
		if (!$this->hasDefinition($name))
		{
			throw new DcGeneralInvalidArgumentException('Data definition ' . $name . ' is not contained.');
		}

		return $this->definitions[$name];
	}
}
