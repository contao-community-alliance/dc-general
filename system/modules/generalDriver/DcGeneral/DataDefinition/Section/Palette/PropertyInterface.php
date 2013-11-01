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

namespace DcGeneral\DataDefinition\Section\Palette;

interface PropertyInterface
{
	/**
	 * Return the name of the property.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the label of the property.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Return the description of the property.
	 *
	 * @return string
	 */
	public function getDescription();

    /**
     * Return the default value of the property.
     * 
     * @return mixed
     */
    public function getDefaultValue();
    
	/**
	 * Determinator if this property can be excluded from access.
	 *
	 * @return bool
	 */
	public function isExcluded();

	/**
	 * Determinator if search is enabled on this property.
	 *
	 * @return bool
	 */
	public function isSearchable();

	/**
	 * Determinator if sorting may be performed on this property.
	 *
	 * @return bool
	 */
	public function isSortable();

	/**
	 * Determinator if filtering may be performed on this property.
	 *
	 * @return bool
	 */
	public function isFilterable();

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
	 * Return the widget type name.
	 *
	 * @return string
	 */
	public function getWidgetType();

    /**
     * Return the valid values of this property.
     * 
     * @return array
     */
    public function getOptions();
    
	/**
	 * Return the explanation of the property.
	 *
	 * @return string
	 */
	public function getExplanation();

	/**
	 * Fetch the extra data of the property.
	 *
	 * @return array
	 */
	public function getExtra();
}
