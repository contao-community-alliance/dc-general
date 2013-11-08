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

namespace DcGeneral\DataDefinition\Section\View;

use DcGeneral\View\ModelFormatterInterface;

interface ListingConfigInterface
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

	const SORT_ASC = 'asc';

	const SORT_DESC = 'desc';

	const SORT_RANDOM = 'random';

	/**
	 * Return the grouping mode.
	 *
	 * @return string
	 */
	public function getGroupingMode();

	/**
	 * The grouping length is used for char or digit grouping and define
	 * how many chars or digits should be respected when group.
	 */
	public function getGroupingLength();

	/**
	 * Return the list sorting mode.
	 * This sorting is applied after grouping and could also be called "in-group sorting".
	 *
	 * @return string
	 */
	public function getSortingMode();

	/**
	 * Get the default sorting fields which are used if the user does not define a sorting.
	 *
	 * @return array
	 */
	public function getDefaultSortingFields();

	/**
	 * Return a list of parent's model property names, which are shown above the item list.
	 *
	 * @return array
	 */
	public function getHeaderPropertyNames();

	/**
	 * Return the icon path to the root item's icon.
	 *
	 * @return string
	 */
	public function getRootIcon();

	/**
	 * Return css classes that should be added to the items container.
	 *
	 * @return string
	 */
	public function getItemCssClass();

	/**
	 * Return the label formatter.
	 *
	 * @return ModelFormatterInterface
	 */
	public function getLabelFormatter();
}
