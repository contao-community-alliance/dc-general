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
 * Interface FilterElementInformationInterface.
 *
 * This interface describes a filter panel element information filtering on a property.
 */
interface FilterElementInformationInterface extends ElementInformationInterface
{
    /**
     * Set the name of the property to filter on.
     *
     * @param string $propertyName The property to filter on.
     *
     * @return FilterElementInformationInterface
     */
    public function setPropertyName($propertyName);

    /**
     * Retrieve the name of the property to filter on.
     *
     * @return string
     */
    public function getPropertyName();
}
