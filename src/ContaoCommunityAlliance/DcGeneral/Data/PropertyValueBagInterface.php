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

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

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
     * @param string $property The name of the property to check.
     *
     * @return bool
     */
    public function hasPropertyValue($property);

    /**
     * Return the value of a property.
     *
     * @param string $property The name of the property to check.
     *
     * @return mixed
     *
     * @throws DcGeneralInvalidArgumentException If the property is not contained within the bag.
     */
    public function getPropertyValue($property);

    /**
     * Set the value of a property.
     *
     * @param string $property The name of the property to set.
     *
     * @param mixed  $value    The value to use.
     *
     * @return PropertyValueBag
     */
    public function setPropertyValue($property, $value);

    /**
     * Remove the value of a property.
     *
     * @param string $property The name of the property to remove.
     *
     * @return PropertyValueBag
     *
     * @throws DcGeneralInvalidArgumentException If the property is not contained within the bag.
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
     * @param string $property The name of the property to check.
     *
     * @return bool
     */
    public function isPropertyValueInvalid($property);

    /**
     * Check if a property value is valid.
     *
     * @param string $property The name of the property to check.
     *
     * @return bool
     */
    public function isPropertyValueValid($property);

    /**
     * Mark a property as invalid and add an error message to the property.
     *
     * @param string             $property The name of the property to mark.
     *
     * @param string|array|mixed $error    The error message to attach for this property.
     *
     * @param bool               $append   Append this error and keep previous errors (optional).
     *
     * @return PropertyValueBag
     */
    public function markPropertyValueAsInvalid($property, $error, $append = true);

    /**
     * Reset the state of a property and remove all errors.
     *
     * @param string $property The name of the property to reset.
     *
     * @return PropertyValueBag
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
     * @param string $property The name of the property to retrieve the errors for.
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
