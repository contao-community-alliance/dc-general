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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Interface SearchElementInformationInterface.
 *
 * This interface describes a search panel element information filtering on properties.
 */
interface SearchElementInformationInterface extends ElementInformationInterface
{
    /**
     * Add a property name to the element.
     *
     * @param string $propertyName The property to allow to search on.
     *
     * @return SearchElementInformationInterface
     */
    public function addProperty($propertyName);

    /**
     * Retrieve the list of properties to allow search on.
     *
     * @return string[]
     */
    public function getPropertyNames();
}
