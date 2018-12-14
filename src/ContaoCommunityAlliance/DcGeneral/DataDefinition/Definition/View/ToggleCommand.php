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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Toggle command - special command for toggling a boolean property between '1' and '' (empty string).
 */
class ToggleCommand extends Command implements ToggleCommandInterface
{
    /**
     * The property name to toggle.
     *
     * @var string
     */
    protected $property;

    /**
     * The toggle command is an inverse command.
     *
     * That means, the toggle does not toggle not-published <-> published, but not-disabled <-> disabled.
     *
     * @var bool
     */
    protected $inverse = false;

    /**
     * {@inheritDoc}
     */
    public function setToggleProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleProperty()
    {
        return $this->property;
    }

    /**
     * {@inheritDoc}
     */
    public function setInverse($inverse)
    {
        $this->inverse = (bool) $inverse;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isInverse()
    {
        return $this->inverse;
    }
}
