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

namespace DcGeneral\DataDefinition\Definition\Properties;

interface PropertyInterface
{
	/**
	 * Return the name of the property.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Set the label language key.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setLabel($value);

	/**
	 * Return the label of the property.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Set the description language key.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setDescription($value);

	/**
	 * Return the description of the property.
	 *
	 * @return string
	 */
	public function getDescription();

	/**
	 * Set the default value of the property.
	 *
	 * @param mixed $value
	 *
	 * @return PropertyInterface
	 */
	public function setDefaultValue($value);

	/**
	 * Return the default value of the property.
	 *
	 * @return mixed
	 */
	public function getDefaultValue();

	/**
	 * Set if the property is excluded from access.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setExcluded($value);

	/**
	 * Determinator if this property is excluded from access.
	 *
	 * @return bool
	 */
	public function isExcluded();

	/**
	 * Set the search determinator.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setSearchable($value);

	/**
	 * Determinator if search is enabled on this property.
	 *
	 * @return bool
	 */
	public function isSearchable();

	/**
	 * Set the sorting determinator.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setSortable($value);

	/**
	 * Determinator if sorting may be performed on this property.
	 *
	 * @return bool
	 */
	public function isSortable();

	/**
	 * Set filtering determinator.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setFilterable($value);

	/**
	 * Determinator if filtering may be performed on this property.
	 *
	 * @return bool
	 */
	public function isFilterable();

	/**
	 * Set the grouping mode.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setGroupingMode($value);

	/**
	 * Return the grouping mode.
	 *
	 * @return string
	 */
	public function getGroupingMode();

	/**
	 * Set the grouping length is used for char or digit grouping and define
	 * how many chars or digits should be respected when group.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setGroupingLength($value);

	/**
	 * The grouping length is used for char or digit grouping and define
	 * how many chars or digits should be respected when group.
	 */
	public function getGroupingLength();

	/**
	 * Set the the list sorting mode.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setSortingMode($value);

	/**
	 * Return the list sorting mode.
	 * This sorting is applied after grouping and could also be called "in-group sorting".
	 *
	 * @return string
	 */
	public function getSortingMode();

	/**
	 * @todo this is view related, should be moved there?
	 * Set the widget type name.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setWidgetType($value);

	/**
	 * @todo this is view related, should be moved there?
	 * Return the widget type name.
	 *
	 * @return string
	 */
	public function getWidgetType();

	/**
	 * Set the valid values of this property.
	 *
	 * @param array $value
	 *
	 * @return PropertyInterface
	 */
	public function setOptions($value);

	/**
	 * Return the valid values of this property.
	 *
	 * @return array|null
	 */
	public function getOptions();

	/**
	 * Set the explanation language string.
	 *
	 * @param string $value
	 *
	 * @return PropertyInterface
	 */
	public function setExplanation($value);

	/**
	 * Return the explanation of the property.
	 *
	 * @return string
	 */
	public function getExplanation();

	/**
	 * Set the extra data of the property.
	 *
	 * @param array $value
	 *
	 * @return PropertyInterface
	 */
	public function setExtra($value);

	/**
	 * Fetch the extra data of the property.
	 *
	 * @return array
	 */
	public function getExtra();
}
