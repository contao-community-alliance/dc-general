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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Interface BasicDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class DefaultPropertiesDefinition implements PropertiesDefinitionInterface
{
	/**
	 * The property definitions contained.
	 *
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
	 *
	 * @throws DcGeneralInvalidArgumentException When an invalid property has been passed or a property with the given
	 *                                           name has already been registered.
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
	 *
	 * @throws DcGeneralInvalidArgumentException When an a property with the given name has not been registered.
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
			throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is not registered.');
		}
		
		unset($this->properties[$name]);
		
		return $this;
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
	 *
	 * @throws DcGeneralInvalidArgumentException When an a property with the given name has not been registered.
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
