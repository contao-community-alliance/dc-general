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
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation for a panel row collection.
 */
class DefaultPanelRowCollection implements PanelRowCollectionInterface
{
    /**
     * The panel rows.
     *
     * @var PanelRowInterface[]
     */
    protected $rows = array();

    /**
     * {@inheritDoc}
     */
    public function getRows()
    {
        $names = array();
        foreach ($this as $row) {
            /** @var PanelRowInterface $row */
            $names[] = $row->getElements();
        }

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function addRow($index = -1)
    {
        $row = new DefaultPanelRow();

        if (($index < 0) || ($this->getRowCount() <= $index)) {
            $this->rows[] = $row;
        } else {
            array_splice($this->rows, $index, 0, array($row));
        }

        return $row;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRowCount()
    {
        return count($this->rows);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When the index does not exist.
     */
    public function getRow($index)
    {
        if (!isset($this->rows[$index])) {
            throw new DcGeneralInvalidArgumentException('Row ' . $index . ' does not exist.');
        }

        return $this->rows[$index];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rows);
    }
}
