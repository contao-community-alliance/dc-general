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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

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
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_NONE = 0;

	/**
	 * Sort by initial letter ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_FIRST_LETTER_ASCENDING = 1;

	/**
	 * Sort by initial letter descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_FIRST_LETTER_DESCENDING = 2;

	/**
	 * Sort by initial two letters ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_TWO_LETTERS_ASCENDING = 3;

	/**
	 * Sort by initial two letters descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_TWO_LETTERS_DESCENDING = 4;

	/**
	 * Sort by day ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_DAY_ASCENDING = 5;

	/**
	 * Sort by day descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_DAY_DESCENDING = 6;

	/**
	 * Sort by month ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_MONTH_ASCENDING = 7;

	/**
	 * Sort by month descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_MONTH_DESCENDING = 8;

	/**
	 * Sort by year ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_YEAR_ASCENDING = 9;

	/**
	 * Sort by year descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DATE_YEAR_DESCENDING = 10;

	/**
	 * Sort ascending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_ASCENDING = 11;

	/**
	 * Sort descending.
	 *
	 * @deprecated not in use anymore.
	 */
	const SORTING_FLAG_DESCENDING = 12;

	/**
	 * Set the default flag to use when no flag has been defined for a certain property.
	 *
	 * @param int $flag The flag to use.
	 *
	 * @return SortElementInformationInterface
	 *
	 * @deprecated not in use anymore.
	 */
	public function setDefaultFlag($flag);

	/**
	 * Get the default flag to use when no flag has been defined for a certain property.
	 *
	 * @return int
	 *
	 * @deprecated not in use anymore.
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
	 *
	 * @deprecated not in use anymore.
	 */
	public function addProperty($propertyName, $flag = 0);

	/**
	 * Determine if the given property has been marked for sorting in the element.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @return SortElementInformationInterface
	 *
	 * @deprecated not in use anymore.
	 */
	public function hasProperty($propertyName);

	/**
	 * Determine if the given property has been marked for sorting in the element.
	 *
	 * @param string $propertyName The name of the property.
	 *
	 * @return SortElementInformationInterface
	 *
	 * @deprecated not in use anymore.
	 */
	public function removeProperty($propertyName);

	/**
	 * Retrieve the list of properties to allow search on.
	 *
	 * @return string[]
	 *
	 * @deprecated not in use anymore.
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
	 *
	 * @deprecated not in use anymore.
	 */
	public function getPropertyFlag($propertyName);
}
