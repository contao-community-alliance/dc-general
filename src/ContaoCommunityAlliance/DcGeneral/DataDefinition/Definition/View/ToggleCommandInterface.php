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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Toggle command - special command interface for toggling a boolean property between '1' and '' (empty string).
 */
interface ToggleCommandInterface extends CommandInterface
{
    /**
     * Set the property to toggle.
     *
     * @param string $property The property name.
     *
     * @return ToggleCommandInterface
     */
    public function setToggleProperty($property);

    /**
     * Set the property to toggle.
     *
     * @return string
     */
    public function getToggleProperty();

    /**
     * Set the inverse state of this toggle command.
     *
     * @param bool $inverse The inverse state.
     *
     * @return ToggleCommandInterface
     */
    public function setInverse($inverse);

    /**
     * Determine the inverse state.
     *
     * @return bool
     */
    public function isInverse();
}
