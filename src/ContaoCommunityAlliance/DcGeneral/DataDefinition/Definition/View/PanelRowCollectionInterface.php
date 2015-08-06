<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * This interface describes a panel row collection.
 */
interface PanelRowCollectionInterface extends \IteratorAggregate
{
    /**
     * Return rows of panel element names.
     *
     * This will return the following for example:
     * array(array('filter[prop1]', 'filter[prop2]'), array('search', 'limit'))
     *
     * Note that each panel element decides its name on its own.
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
     * Delete a row from the collection.
     *
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
     */
    public function getRow($index);
}
