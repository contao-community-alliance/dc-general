<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Toggle command - special command interface for toggling a boolean property between '1' and '' (empty string).
 *
 * @package DcGeneral\DataDefinition\Definition\View
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
