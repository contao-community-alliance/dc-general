<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Interfaces;


interface Container
{
	/**
	 * Return the name of the definition.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the name of the callback provider class to use.
	 *
	 * @return string
	 */
	public function getCallbackProviderClass();

	/**
	 * Retrieve information about a property.
	 *
	 * @param string $strProperty The name of the property.
	 *
	 * @return Property
	 */
	public function getProperty($strProperty);

	/**
	 * Retrieve the names of all defined properties.
	 *
	 * @return string[]
	 */
	public function getPropertyNames();

	/**
	 * Retrieve the panel layout.
	 *
	 * Returns an array of arrays of which each level 1 array is a separate group.
	 *
	 * @return array
	 */
	public function getPanelLayout();

	/**
	 * Retrieve the names of properties to use for secondary sorting.
	 *
	 * @return string[]
	 */
	public function getAdditionalSorting();

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
	public function getSortingMode();

	/**
	 * Retrieve information about a operation.
	 *
	 * @param string $strOperation The name of the operation.
	 *
	 * @return Operation
	 */
	public function getOperation($strOperation);

	/**
	 *
	 *
	 * @return \DcGeneral\DataDefinition\Interfaces\Operation
	 */
	public function getOperationNames();

	/**
	 * Boolean flag determining if this data container is closed.
	 *
	 * True means, there may not be any records added or deleted, false means there may be any record appended or
	 * deleted..
	 *
	 * @return bool
	 */
	public function isClosed();

	/**
	 * Boolean flag determining if this data container is editable.
	 *
	 * True means, the data records may be edited.
	 *
	 * @return bool
	 */
	public function isEditable();

	/**
	 * Retrieve the root condition for the current table.
	 *
	 * @return \DcGeneral\DataDefinition\Interfaces\RootCondition
	 */
	public function getRootCondition();

	/**
	 * Retrieve the parent child condition for the current table.
	 *
	 * @param string $strSrcTable The parenting table.
	 *
	 * @param string $strDstTable The child table.
	 *
	 * @return \DcGeneral\DataDefinition\Interfaces\ParentChildCondition
	 */
	public function getChildCondition($strSrcTable, $strDstTable);

	/**
	 * Retrieve the parent child conditions for the current table.
	 *
	 * @param string $strSrcTable The parenting table for which child conditions shall be assembled for (optional).
	 *
	 * @return \DcGeneral\DataDefinition\Interfaces\ParentChildCondition[]
	 */
	public function getChildConditions($strSrcTable = '');
}
