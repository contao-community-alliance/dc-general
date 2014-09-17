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
 * Interface ListingConfigInterface.
 *
 * This interface describes a property.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
interface ListingConfigInterface
{
    /**
     * Do not group.
     *
     * @deprecated
     */
    const GROUP_NONE = GroupAndSortingInformationInterface::GROUP_NONE;

    /**
     * Group by characters, the max char count depend on the mode length
     * (which is 1 by default for char grouping).
     *
     * @deprecated
     */
    const GROUP_CHAR = GroupAndSortingInformationInterface::GROUP_CHAR;

    /**
     * Group by digits, the max digit count depend on the mode length
     * (which is infinity by default for digit grouping)..
     *
     * @deprecated
     */
    const GROUP_DIGIT = GroupAndSortingInformationInterface::GROUP_DIGIT;

    /**
     * Sort by day from datetime property.
     *
     * @deprecated
     */
    const GROUP_DAY = GroupAndSortingInformationInterface::GROUP_DAY;

    /**
     * Sort by week day from datetime property.
     *
     * @deprecated
     */
    const GROUP_WEEKDAY = GroupAndSortingInformationInterface::GROUP_WEEKDAY;

    /**
     * Sort by week of the year from datetime property.
     *
     * @deprecated
     */
    const GROUP_WEEK = GroupAndSortingInformationInterface::GROUP_WEEK;

    /**
     * Sort by month from datetime property.
     *
     * @deprecated
     */
    const GROUP_MONTH = GroupAndSortingInformationInterface::GROUP_MONTH;

    /**
     * Sort by year from datetime property.
     *
     * @deprecated
     */
    const GROUP_YEAR = GroupAndSortingInformationInterface::GROUP_YEAR;

    /**
     * Sort ascending.
     *
     * @deprecated
     */
    const SORT_ASC = GroupAndSortingInformationInterface::SORT_ASC;

    /**
     * Sort descending.
     *
     * @deprecated
     */
    const SORT_DESC = GroupAndSortingInformationInterface::SORT_DESC;

    /**
     * Shuffle all records instead of sorting.
     *
     * @deprecated
     */
    const SORT_RANDOM = GroupAndSortingInformationInterface::SORT_RANDOM;

    /**
     * Set the grouping mode.
     *
     * @param string $value The new mode.
     *
     * @return ListingConfigInterface
     *
     * @deprecated
     */
    public function setGroupingMode($value);

    /**
     * Return the grouping mode.
     *
     * @return string
     *
     * @deprecated
     */
    public function getGroupingMode();

    /**
     * Set the grouping length.
     *
     * @param int $value The new value.
     *
     * @return ListingConfigInterface
     *
     * @deprecated
     */
    public function setGroupingLength($value);

    /**
     * The grouping length is used for char or digit grouping.
     *
     * It defines how many chars or digits should be respected when group mode is GROUP_CHAR.
     *
     * @return int
     *
     * @deprecated
     */
    public function getGroupingLength();

    /**
     * Set the list sorting mode.
     *
     * @param string $value The new value.
     *
     * @return ListingConfigInterface
     *
     * @deprecated
     */
    public function setSortingMode($value);

    /**
     * Return the list sorting mode.
     *
     * This sorting is applied after grouping and could also be called "in-group sorting".
     *
     * @return string
     *
     * @deprecated
     */
    public function getSortingMode();

    /**
     * Set the default sorting fields.
     *
     * @param array $value The sorting fields to use.
     *
     * @return ListingConfigInterface
     *
     * @deprecated
     */
    public function setDefaultSortingFields($value);

    /**
     * Get the default sorting fields which are used if the user does not define a sorting.
     *
     * @return array
     *
     * @deprecated
     */
    public function getDefaultSortingFields();

    /**
     * Set the grouping and sorting definitions.
     *
     * @param GroupAndSortingDefinitionCollectionInterface $definition The definition to use.
     *
     * @return ListingConfigInterface
     */
    public function setGroupAndSortingDefinition($definition);

    /**
     * Retrieve the grouping and sorting definitions.
     *
     * @return GroupAndSortingDefinitionCollectionInterface
     */
    public function getGroupAndSortingDefinition();

    /**
     * Set the list of parent's model property names.
     *
     * @param array $value The property names to use.
     *
     * @return ListingConfigInterface
     */
    public function setHeaderPropertyNames($value);

    /**
     * Return a list of parent's model property names, which are shown above the item list.
     *
     * @return array
     */
    public function getHeaderPropertyNames();

    /**
     * Set the icon path to the root item's icon.
     *
     * @param string $value The path to the icon.
     *
     * @return ListingConfigInterface
     */
    public function setRootIcon($value);

    /**
     * Return the icon path to the root item's icon.
     *
     * @return string
     */
    public function getRootIcon();

    /**
     * Set the root label.
     *
     * @param string $value The new label.
     *
     * @return ListingConfigInterface
     */
    public function setRootLabel($value);

    /**
     * Get the root label.
     *
     * @return string
     */
    public function getRootLabel();

    /**
     * Set the css classes that should be added to the items container.
     *
     * @param string $value The CSS class to use.
     *
     * @return ListingConfigInterface
     */
    public function setItemCssClass($value);

    /**
     * Return css classes that should be added to the items container.
     *
     * @return string
     */
    public function getItemCssClass();

    /**
     * Set the label formatter.
     *
     * @param string                        $providerName The name of the data provider.
     *
     * @param ModelFormatterConfigInterface $value        The model formatter to use.
     *
     * @return ListingConfigInterface
     */
    public function setLabelFormatter($providerName, $value);

    /**
     * Determine if the label formatter is present for a certain data provider.
     *
     * @param string $providerName The name of the data provider.
     *
     * @return bool
     */
    public function hasLabelFormatter($providerName);

    /**
     * Return the label formatter for a certain data provider.
     *
     * @param string $providerName The name of the data provider.
     *
     * @return ModelFormatterConfigInterface
     */
    public function getLabelFormatter($providerName);

    /**
     * Set if the listing shall be in table columns.
     *
     * @param bool $value The flag.
     *
     * @return ListingConfigInterface
     */
    public function setShowColumns($value);

    /**
     * Get if the listing shall be in table columns.
     *
     * @return bool
     */
    public function getShowColumns();
}
