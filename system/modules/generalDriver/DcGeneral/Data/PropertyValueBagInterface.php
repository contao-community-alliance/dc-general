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
interface PropertyValueBagInterface
	extends
	\IteratorAggregate,
	\Countable,
	\ArrayAccess
{
	/**
	 * Check if a property exists in this bag.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function hasPropertyValue($property);

	/**
	 * Return the value of a property.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function getPropertyValue($property);

	/**
	 * Set the value of a property.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function setPropertyValue($property, $value);

	/**
	 * Remove the value of a property.
	 *
	 * @param string $property
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function removePropertyValue($property);

	/**
	 * Check if this bag contains invalid property values.
	 *
	 * @return bool
	 */
	public function hasInvalidPropertyValues();

	/**
	 * Check if this bag contains no invalid property values.
	 *
	 * @return bool
	 */
	public function hasNoInvalidPropertyValues();

	/**
	 * Check if a property value is invalid.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function isPropertyValueInvalid($property);

	/**
	 * Check if a property value is valid.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function isPropertyValueValid($property);

	/**
	 * Mark a property as invalid and add an error message to the property.
	 *
	 * @param string $property
	 * @param string|array|mixed $error
	 * @param bool $append Append this error and keep previous errors.
	 */
	public function markPropertyValueAsInvalid($property, $error, $append = true);

	/**
	 * Reset the state of a property and remove all errors.
	 *
	 * @param string $property
	 */
	public function resetPropertyValueErrors($property);

	/**
	 * Get a list of all property names, whose values are invalid.
	 *
	 * @return string[]
	 */
	public function getInvalidPropertyNames();

	/**
	 * Return all errors of an invalid property value.
	 *
	 * @param string $property
	 *
	 * @return array
	 */
	public function getPropertyValueErrors($property);

	/**
	 * Get an associative array of properties and their errors, whose values are invalid.
	 *
	 * @return array
	 */
	public function getInvalidPropertyErrors();

	/**
	 * Exports the {@link PropertyValueBag} to an array.
	 *
	 * @return array
	 */
	public function getArrayCopy();
}
