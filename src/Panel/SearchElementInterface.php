<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * This interface describes a search panel element.
 */
interface SearchElementInterface extends PanelElementInterface
{
    /**
     * Add a property that can be searched.
     *
     * @param string $strProperty The property to allow to search on.
     *
     * @return SearchElementInterface
     */
    public function addProperty($strProperty);

    /**
     * Retrieve the list of properties to allow search on.
     *
     * @return string[]
     */
    public function getPropertyNames();

    /**
     * This activates a property for search.
     *
     * @param string $strProperty The property to activate search on.
     *
     * @return SearchElementInterface
     */
    public function setSelectedProperty($strProperty = '');

    /**
     * Retrieves the property currently defined to be searched on.
     *
     * @return string
     */
    public function getSelectedProperty();

    /**
     * Set the value to search for.
     *
     * @param string $mixValue The value to search for.
     *
     * @return SearchElementInterface
     */
    public function setValue($mixValue = null);

    /**
     * Retrieve the value to be searched for.
     *
     * @return string
     */
    public function getValue();
}
