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

use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Interface BasicDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class DefaultPropertiesDefinition implements PropertiesDefinitionInterface
{
	/**
	 * @var PropertyInterface[]
	 */
	protected $properties = array();

	/**
	 * {@inheritdoc}
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertyNames()
	{
		return array_keys($this->properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addProperty($property)
	{
		if (!($property instanceof PropertyInterface))
		{
			throw new DcGeneralInvalidArgumentException('Passed value is not an instance of PropertyInterface.');
		}

		$name = $property->getName();

		if ($this->hasProperty($name))
		{
			throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is already registered.');
		}

		$this->properties[$name] = $property;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeProperty($property)
	{
		if ($property instanceof PropertyInterface)
		{
			$name = $property->getName();
		}
		else
		{
			$name = $property;
		}

		if (!$this->hasProperty($name))
		{
			throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is already registered.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasProperty($name)
	{
		return isset($this->properties[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperty($name)
	{
		if (!$this->hasProperty($name))
		{
			throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is not registered.');
		}

		return $this->properties[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->properties);
	}
}
