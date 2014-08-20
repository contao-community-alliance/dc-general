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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * This interface defines a grouping and sorting information for the view.
 */
interface GroupAndSortingInformationInterface
{
	/**
	 * Do not group.
	 */
	const GROUP_NONE = 'none';

	/**
	 * Group by characters, the max char count depend on the mode length
	 * (which is 1 by default for char grouping).
	 */
	const GROUP_CHAR = 'char';

	/**
	 * Group by digits, the max digit count depend on the mode length
	 * (which is infinity by default for digit grouping)..
	 */
	const GROUP_DIGIT = 'digit';

	/**
	 * Sort by day from datetime property.
	 */
	const GROUP_DAY = 'day';

	/**
	 * Sort by week day from datetime property.
	 */
	const GROUP_WEEKDAY = 'weekday';

	/**
	 * Sort by week of the year from datetime property.
	 */
	const GROUP_WEEK = 'week';

	/**
	 * Sort by month from datetime property.
	 */
	const GROUP_MONTH = 'month';

	/**
	 * Sort by year from datetime property.
	 */
	const GROUP_YEAR = 'year';

	/**
	 * Sort ascending.
	 */
	const SORT_ASC = 'asc';

	/**
	 * Sort descending.
	 */
	const SORT_DESC = 'desc';

	/**
	 * Shuffle all records instead of sorting.
	 */
	const SORT_RANDOM = 'random';

	/**
	 * Set the name of the property
	 *
	 * @param string $property The property name.
	 *
	 * @return GroupAndSortingInformationInterface
	 */
	public function setProperty($property);

	/**
	 * Get the property name.
	 *
	 * @return string
	 */
	public function getProperty();

	/**
	 * Set the grouping mode.
	 *
	 * @param string $value The new mode.
	 *
	 * @return GroupAndSortingInformationInterface
	 */
	public function setGroupingMode($value);

	/**
	 * Return the grouping mode.
	 *
	 * @return string
	 */
	public function getGroupingMode();

	/**
	 * Set the grouping length.
	 *
	 * @param int $value The new value.
	 *
	 * @return GroupAndSortingInformationInterface
	 */
	public function setGroupingLength($value);

	/**
	 * The grouping length is used for char or digit grouping.
	 *
	 * It defines how many chars or digits should be respected when group mode is GROUP_CHAR.
	 *
	 * @return int
	 */
	public function getGroupingLength();

	/**
	 * Set the list sorting mode.
	 *
	 * @param string $value The new value.
	 *
	 * @return GroupAndSortingInformationInterface
	 */
	public function setSortingMode($value);

	/**
	 * Return the list sorting mode.
	 *
	 * This sorting is applied after grouping and could also be called "in-group sorting".
	 *
	 * @return string
	 */
	public function getSortingMode();
}
