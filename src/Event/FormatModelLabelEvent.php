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
