<?php

namespace DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Interface SortElementInformationInterface.
 *
 * This interface describes a sort panel element information for sorting by properties.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
interface SortElementInformationInterface extends ElementInformationInterface
{
	/**
	 * Do not sort.
	 */
	const SORTING_FLAG_NONE = 0;

	/**
	 * Sort by initial letter ascending.
	 */
	const SORTING_FLAG_FIRST_LETTER_ASCENDING = 1;

	/**
	 * Sort by initial letter descending.
	 */
	const SORTING_FLAG_FIRST_LETTER_DESCENDING = 2;

	/**
	 * Sort by initial two letters ascending.
	 */
	const SORTING_FLAG_TWO_LETTERS_ASCENDING = 3;

	/**
	 * Sort by initial two letters descending.
	 */
	const SORTING_FLAG_TWO_LETTERS_DESCENDING = 4;

	/**
	 * Sort by day ascending.
	 */
	const SORTING_FLAG_DATE_DAY_ASCENDING = 5;

	/**
	 * Sort by day descending.
	 */
	const SORTING_FLAG_DATE_DAY_DESCENDING = 6;

	/**
	 * Sort by month ascending.
	 */
	const SORTING_FLAG_DATE_MONTH_ASCENDING = 7;

	/**
	 * Sort by month descending.
	 */
	const SORTING_FLAG_DATE_MONTH_DESCENDING = 8;

	/**
	 * Sort by year ascending.
	 */
	const SORTING_FLAG_DATE_YEAR_ASCENDING = 9;

	/**
	 * Sort by year descending.
	 */
	const SORTING_FLAG_DATE_YEAR_DESCENDING = 10;

	/**
	 * Sort ascending.
	 */
	const SORTING_FLAG_ASCENDING = 11;

	/**
	 * Sort descending.
	 */
	const SORTING_FLAG_DESCENDING = 12;

	/**
	 * Set the default flag to use when no flag has been defined for a certain property.
	 *
	 * @param int $flag The flag to use.
	 *
	 * @return SortElementInformationInterface
	 */
	public function setDefaultFlag($flag);

	/**
	 * Get the default flag to use when no flag has been defined for a certain property.
	 *
	 * @return int
	 */
	public function getDefaultFlag();

	/**
	 * Add a property for sorting.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @param int    $flag         If the default of 0 is passed, the default flag will get used.
	 *
	 * @return SortElementInformationInterface
	 */
	public function addProperty($propertyName, $flag = 0);

	/**
	 * Determine if the given property has been marked for sorting in the element.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @return SortElementInformationInterface
	 */
	public function hasProperty($propertyName);

	/**
	 * Determine if the given property has been marked for sorting in the element.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @return SortElementInformationInterface
	 */
	public function removeProperty($propertyName);

	/**
	 * Retrieve the list of properties to allow search on.
	 *
	 * @return string[]
	 */
	public function getPropertyNames();

	/**
	 * Get the flag to use for a property.
	 *
	 * When a flag has been defined for a certain property that flag is being used otherwise the the default flag will
	 * get returned.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @return int
	 */
	public function getPropertyFlag($propertyName);
}
