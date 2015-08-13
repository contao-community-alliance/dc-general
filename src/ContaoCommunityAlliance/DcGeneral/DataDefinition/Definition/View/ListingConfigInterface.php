<?php
/**
 * PHP version 5
 *
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
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShowColumns();
}
