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

namespace DcGeneral\DataDefinition;


use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\DataDefinition\Section\ContainerSectionInterface;
use DcGeneral\DataDefinition\Section\DataProviderSectionInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

interface ContainerInterface
{
	/**
	 * Return the name of the container.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Check if this container has a section.
	 *
	 * @param string $sectionName
	 *
	 * @return bool
	 */
	public function hasSection($sectionName);

	/**
	 * Clear all sections from this container.
	 *
	 * @return ContainerInterface
	 */
	public function clearSections();

	/**
	 * Set the sections of this container.
	 *
	 * @param ContainerSectionInterface[] $sections
	 *
	 * @return ContainerInterface
	 */
	public function setSections(array $sections);

	/**
	 * Add multiple sections to this container.
	 *
	 * @param ContainerSectionInterface[] $sections
	 *
	 * @return ContainerInterface
	 */
	public function addSections(array $sections);

	/**
	 * Set a sections of this container.
	 *
	 * @param string $sectionName
	 * @param ContainerSectionInterface $section
	 *
	 * @return ContainerInterface
	 */
	public function setSection($sectionName, ContainerSectionInterface $section);

	/**
	 * Remove a sections from this container.
	 *
	 * @param string $sectionName
	 *
	 * @return ContainerInterface
	 */
	public function removeSection($sectionName);

	/**
	 * Get a sections of this container.
	 *
	 * @param string $sectionName
	 *
	 * @return ContainerSectionInterface
	 *
	 * @throws DcGeneralInvalidArgumentException Is thrown when there is no section with this name.
	 */
	public function getSection($sectionName);

	/**
	 * Get a list of all section names in this container.
	 *
	 * @return array
	 */
	public function getSectionNames();

	/**
	 * Convenience method to check if a basic section is contained.
	 *
	 * @return BasicSectionInterface
	 */
	public function hasBasicSection();

	/**
	 * Convenience method to set the basic section.
	 *
	 * @param Section\BasicSectionInterface $section
	 *
	 * @return ContainerSectionInterface
	 */
	public function setBasicSection(BasicSectionInterface $section);

	/**
	 * Convenience method to retrieve the basic section.
	 *
	 * @return BasicSectionInterface
	 */
	public function getBasicSection();

	/**
	 * Convenience method to check if a data provider section is contained.
	 *
	 * @return BasicSectionInterface
	 */
	public function hasDataProviderSection();

	/**
	 * Convenience method to set the data provider section.
	 *
	 * @param DataProviderSectionInterface $section
	 *
	 * @return DataProviderSectionInterface
	 */
	public function setDataProviderSection(DataProviderSectionInterface $section);

	/**
	 * Convenience method to retrieve the data provider section.
	 *
	 * @return DataProviderSectionInterface
	 */
	public function getDataProviderSection();



	// TODO extract

	/**
	 * Return the name of the parenting definition.
	 *
	 * @return string
	 */
	//public function getParentDriverName();

	/**
	 * Return the label of the definition.
	 *
	 * @return string
	 */
	//public function getLabel();

	/**
	 * Return the icon of the definition.
	 *
	 * @return string
	 */
	//public function getIcon();

	/**
	 * Return the name of the callback provider class to use.
	 *
	 * @return string
	 *
	 * @deprecated It is suggested to use events instead of callbacks now.
	 */
	//public function getCallbackProviderClass();

	/**
	 * Check if this container has any properties.
	 *
	 * @return bool
	 */
	//public function hasProperties();

	/**
	 * Retrieve all defined properties.
	 *
	 * @return PropertyInterface[]
	 */
	//public function getProperties();

	/**
	 * Retrieve the names of all defined properties.
	 *
	 * @return string[]
	 */
	//public function getPropertyNames();

	/**
	 * Retrieve information about a property.
	 *
	 * @param string $strProperty The name of the property.
	 *
	 * @return PropertyInterface
	 */
	//public function getProperty($strProperty);

	/**
	 * Check if this container has editable properties.
	 *
	 * @return string[]
	 */
	//public function hasEditableProperties();

	/**
	 * Retrieve all editable properties.
	 *
	 * @return PropertyInterface[]
	 */
	//public function getEditableProperties();

	/**
	 * Retrieve the names of all editable properties.
	 *
	 * @return string[]
	 */
	//public function getEditablePropertyNames();

	/**
	 * Retrieve the panel layout.
	 *
	 * Returns an array of arrays of which each level 1 array is a separate group.
	 *
	 * @return array
	 */
	//public function getPanelLayout();

	/**
	 * Retrieve the name of the property to use for the main/default sorting.
	 *
	 * @return string
	 */
	//public function getFirstSorting();

	/**
	 * Retrieve the names of properties to use for secondary sorting.
	 *
	 * @return string[]
	 */
	//public function getAdditionalSorting();

	/**
	 * Retrieve the sorting mode for the data container.
	 *
	 * Values are:
	 * 0 Records are not sorted
	 * 1 Records are sorted by a fixed field
	 * 2 Records are sorted by a switchable field
	 * 3 Records are sorted by the parent table
	 * 4 Displays the child records of a parent record (see style sheets module)
	 * 5 Records are displayed as tree (see site structure)
	 * 6 Displays the child records within a tree structure (see articles module)
	 *
	 * @return int
	 */
	//public function getSortingMode();

	/**
	 * Retrieve the sorting flag for the data container.
	 *
	 *  1 Sort by initial letter ascending
	 *  2 Sort by initial letter descending
	 *  3 Sort by initial two letters ascending
	 *  4 Sort by initial two letters descending
	 *  5 Sort by day ascending
	 *  6 Sort by day descending
	 *  7 Sort by month ascending
	 *  8 Sort by month descending
	 *  9 Sort by year ascending
	 * 10 Sort by year descending
	 * 11 Sort ascending
	 * 12 Sort descending
	 *
	 * @return int
	 */
	//public function getSortingFlag();

	/**
	 * Retrieve information about a operation.
	 *
	 * @param string $strOperation The name of the operation.
	 *
	 * @return \DcGeneral\DataDefinition\OperationInterface
	 */
	//public function getOperation($strOperation);

	/**
	 *
	 *
	 * @return string[]
	 */
	//public function getOperationNames();

	/**
	 * Boolean flag determining if this data container is closed.
	 *
	 * True means, there may not be any records added or deleted, false means there may be any record appended or
	 * deleted..
	 *
	 * @return bool
	 */
	//public function isClosed();

	/**
	 * Boolean flag determining if this data container is editable.
	 *
	 * True means, the data records may be edited.
	 *
	 * @return bool
	 */
	//public function isEditable();

	/**
	 * Boolean flag determining if this data container is deletable.
	 *
	 * True means, the data records may be deleted.
	 *
	 * @return bool
	 */
	//public function isDeletable();

	/**
	 * Determines if new entries may be created within this data container.
	 *
	 * True means new entries may be created, false prohibits creation of new entries.
	 *
	 * @return bool
	 */
	//public function isCreatable();

	/**
	 * Determines if the view shall switch automatically into edit mode.
	 * This most likely only affects parenting modes like trees etc.
	 *
	 * @return bool
	 */
	//public function isSwitchToEdit();

	/**
	 * Allows you to disable the group headers in list view and parent view.
	 *
	 * True means, the data records will not be grouped with headers.
	 *
	 * @return bool
	 */
	//public function isGroupingDisabled();

	/**
	 * Retrieve the root condition for the current table.
	 *
	 * @return \DcGeneral\DataDefinition\RootConditionInterface
	 */
	//public function getRootCondition();

	/**
	 * Retrieve the parent child condition for the current table.
	 *
	 * @param string $strSrcTable The parenting table.
	 *
	 * @param string $strDstTable The child table.
	 *
	 * @return \DcGeneral\DataDefinition\ParentChildConditionInterface
	 */
	//public function getChildCondition($strSrcTable, $strDstTable);

	/**
	 * Retrieve the parent child conditions for the current table.
	 *
	 * @param string $strSrcTable The parenting table for which child conditions shall be assembled for (optional).
	 *
	 * @return \DcGeneral\DataDefinition\ParentChildConditionInterface[]
	 */
	//public function getChildConditions($strSrcTable = '');

	/**
	 * Retrieve the label information for listing of datasets.
	 *
	 * @return \DcGeneral\DataDefinition\ListLabelInterface
	 */
	//public function getListLabel();

	/**
	 * One or more properties that will be shown in the header element (sorting mode 4 only).
	 *
	 * @return array
	 */
	//public function getParentViewHeaderProperties();

	/**
	 * Return the additional filters to be applied for retrieving data.
	 *
	 * This is some custom filter defined by the admin or something like that.
	 *
	 * @return array
	 */
	//public function getAdditionalFilter();

	/**
	 * Get the palettes of this container.
	 *
	 * @return PaletteCollectionInterface
	 */
	//public function getPalettes();
}
