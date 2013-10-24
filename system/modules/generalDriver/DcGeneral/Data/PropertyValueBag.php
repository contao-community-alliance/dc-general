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

namespace DcGeneral\Data;

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * A generic bag containing properties and their values.
 */
class PropertyValueBag implements PropertyValueBagInterface
{
	/**
	 * All properties and its values in this bag.
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * All properties that are marked as invalid and their error messages.
	 *
	 * @var array
	 */
	protected $errors = array();

	function __construct($properties = null)
	{
		if (is_array($properties) || $properties instanceof \Traversable)
		{
			foreach ($properties as $property => $value) {
				$this->setPropertyValue($property, $value);
			}
		}
		else if ($properties !== null)
		{
			throw new DcGeneralInvalidArgumentException('The parameter $properties does not contain any properties nor values');
		}
	}

	/**
	 * Check if a property exists, otherwise through an exception.
	 *
	 * @param string $property
	 *
	 * @throws DcGeneralInvalidArgumentException
	 * @internal
	 */
	protected function requirePropertyValue($property)
	{
		if (!$this->hasPropertyValue($property))
		{
			throw new DcGeneralInvalidArgumentException('The property ' . $property . ' does not exists');
		}
	}

	/**
	 * Check if a property exists in this bag.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function hasPropertyValue($property)
	{
		return array_key_exists($property, $this->properties);
	}

	/**
	 * Return the value of a property.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function getPropertyValue($property)
	{
		$this->requirePropertyValue($property);
		return $this->properties[$property];
	}

	/**
	 * Set the value of a property.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function setPropertyValue($property, $value)
	{
		$this->properties[$property] = $value;
	}

	/**
	 * Remove the value of a property.
	 *
	 * @param string $property
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function removePropertyValue($property)
	{
		$this->requirePropertyValue($property);
		unset($this->properties[$property]);
	}

	/**
	 * Check if this bag contains invalid property values.
	 *
	 * @return bool
	 */
	public function hasInvalidPropertyValues()
	{
		return (bool) $this->errors;
	}

	/**
	 * Check if this bag contains no invalid property values.
	 *
	 * @return bool
	 */
	public function hasNoInvalidPropertyValues()
	{
		return !$this->errors;
	}

	/**
	 * Check if a property value is invalid.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function isPropertyValueInvalid($property)
	{
		$this->requirePropertyValue($property);
		return (bool) $this->errors[$property];
	}

	/**
	 * Check if a property value is valid.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function isPropertyValueValid($property)
	{
		$this->requirePropertyValue($property);
		return !$this->errors[$property];
	}

	/**
	 * Mark a property as invalid and add an error message to the property.
	 *
	 * @param string $property
	 * @param string|array|mixed $error
	 * @param bool $append Append this error and keep previous errors.
	 */
	public function markPropertyValueAsInvalid($property, $error, $append = true)
	{
		$this->requirePropertyValue($property);

		if (!isset($this->errors[$property]) || !$append)
		{
			$this->errors[$property] = array();
		}

		foreach ((array) $error as $singleError)
		{
			$this->errors[$property][] = $singleError;
		}
	}

	/**
	 * Reset the state of a property and remove all errors.
	 *
	 * @param string $property
	 */
	public function resetPropertyValueErrors($property)
	{
		$this->requirePropertyValue($property);
		unset($this->errors[$property]);
	}

	/**
	 * Get a list of all property names, whose values are invalid.
	 *
	 * @return string[]
	 */
	public function getInvalidPropertyNames()
	{
		return array_keys($this->errors);
	}

	/**
	 * Return all errors of an invalid property value.
	 *
	 * @param string $property
	 *
	 * @return array
	 */
	public function getPropertyValueErrors($property)
	{
		$this->requirePropertyValue($property);
		return (array) $this->errors[$property];
	}

	/**
	 * Get an associative array of properties and their errors, whose values are invalid.
	 *
	 * @return array
	 */
	public function getInvalidPropertyErrors()
	{
		return $this->errors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return $this->hasPropertyValue($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->getPropertyValue($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->setPropertyValue($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		$this->removePropertyValue($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	function __isset($name)
	{
		return $this->hasPropertyValue($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __get($name)
	{
		return $this->getPropertyValue($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __set($name, $value)
	{
		$this->setPropertyValue($name, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	function __unset($name)
	{
		$this->removePropertyValue($name);
	}

	/**
	 * Exports the {@link PropertyValueBag} to an array.
	 *
	 * @return array
	 */
	public function getArrayCopy()
	{
		return $this->properties;
	}
}
