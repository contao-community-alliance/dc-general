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

interface PanelRowCollectionInterface extends \IteratorAggregate
{
	/**
	 * Return rows of panel element names.
	 *
	 * This will return the following for example:
	 * array(array('filter[prop1]', 'filter[prop2]'), array('search', 'limit'))
	 *
	 * @return array
	 */
	public function getRows();

	/**
	 * Add a new row - optionally at the given position.
	 *
	 * If the given position is zero or any other positive value, the new row will get placed at the given position.
	 * If the index is negative or greater than the total amount of rows present, the new row will get placed at the end
	 * of the list.
	 *
	 * @param int $index Target position for the new row.
	 *
	 * @return PanelRowInterface
	 */
	public function addRow($index = -1);

	/**
	 * @param int $index Remove the row with the given index.
	 *
	 * @return PanelRowCollectionInterface
	 */
	public function deleteRow($index);

	/**
	 * Retrieve the amount of rows.
	 *
	 * @return int
	 */
	public function getRowCount();

	/**
	 * Retrieve the row at the given position.
	 *
	 * If the given index is out of bounds (less than zero or greater than the amount of rows) an exception is fired.
	 *
	 * @param int $index Position of the row.
	 *
	 * @return PanelRowInterface
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 */
	public function getRow($index);

	/**
	 * @return PanelRowInterface[]
	 */
	public function getIterator();
}
