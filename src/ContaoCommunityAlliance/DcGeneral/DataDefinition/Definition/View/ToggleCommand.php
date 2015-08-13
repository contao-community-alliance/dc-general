<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Toggle command - special command for toggling a boolean property between '1' and '' (empty string).
 *
 * @package DcGeneral\DataDefinition\Definition\View
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
