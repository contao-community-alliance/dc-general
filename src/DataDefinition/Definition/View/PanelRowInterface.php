<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\ElementInformationInterface;

/**
 * This interface describes a panel row definition.
 */
interface PanelRowInterface extends \IteratorAggregate
{
    /**
     * Return the names of the contained panel elements.
     *
     * This will return the following for example:
     * array('filter[prop1]', 'filter[prop2]', 'search', 'limit')
     *
     * @return array
     */
    public function getElements();

    /**
     * Add an element at the end of the row or - optionally - at the given position.
     *
     * If the given position is zero or any other positive value, the element will get placed at the given position.
     * If the index is negative or greater than the total amount of rows present, the new element will get placed at the
     * end of the list.
     *
     * @param ElementInformationInterface $element The element to add.
     *
     * @param int                         $index   Target position for the element.
     *
     * @return PanelRowInterface
     */
    public function addElement(ElementInformationInterface $element, $index = -1);

    /**
     * Remove the element with the given index (if numeric) or name (if string).
     *
     * @param int|string|ElementInformationInterface $indexOrNameOrInstance Element name or numeric index in the row.
     *
     * @return PanelRowInterface
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function deleteElement($indexOrNameOrInstance);

    /**
     * Check if the given element instance or an element with the given name is in the row.
     *
     * Throws an exception when an invalid value has been passed.
     *
     * @param ElementInformationInterface|string $instanceOrName The element instance or the name of an element to
     *                                                           check.
     *
     * @return bool
     */
    public function hasElement($instanceOrName);

    /**
     * Retrieve the amount of elements.
     *
     * @return int
     */
    public function getCount();

    /**
     * Retrieve the element with the given index (if numeric) or name (if string).
     *
     * @param int|string $indexOrName Element name or numeric index in the row.
     *
     * @return ElementInformationInterface
     */
    public function getElement($indexOrName);
}
