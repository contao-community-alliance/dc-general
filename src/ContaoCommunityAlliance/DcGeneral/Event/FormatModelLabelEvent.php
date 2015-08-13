<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

/**
 * This event is emitted when the model label must be formatted.
 */
class FormatModelLabelEvent extends AbstractModelAwareEvent
{
    /**
     * The model label.
     *
     * @var array
     */
    protected $label = null;

    /**
     * Returns the model label.
     *
     * @return array
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the model label.
     *
     * @param array $label The model label.
     *
     * @return static
     */
    public function setLabel(array $label)
    {
        $this->label = $label;
        return $this;
    }
}
