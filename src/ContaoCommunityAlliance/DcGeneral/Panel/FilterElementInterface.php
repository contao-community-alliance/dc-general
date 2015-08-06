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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * This interface describes a filter panel element.
 */
interface FilterElementInterface extends PanelElementInterface
{
    /**
     * Set the property name to filter on.
     *
     * @param string $strProperty The property to filter on.
     *
     * @return FilterElementInterface
     */
    public function setPropertyName($strProperty);

    /**
     * Retrieve the property name to filter on.
     *
     * @return string
     */
    public function getPropertyName();

    /**
     * Set the value to filter for.
     *
     * @param mixed $mixValue The value to filter for.
     *
     * @return FilterElementInterface
     */
    public function setValue($mixValue);

    /**
     * Retrieve the value to filter for.
     *
     * @return mixed
     */
    public function getValue();
}
