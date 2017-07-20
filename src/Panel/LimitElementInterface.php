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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * This interface describes a panel limit element.
 */
interface LimitElementInterface extends PanelElementInterface
{
    /**
     * Set the offset to use in this element.
     *
     * @param int $intOffset The offset.
     *
     * @return LimitElementInterface
     */
    public function setOffset($intOffset);

    /**
     * Get the offset to use in this element.
     *
     * @return int
     */
    public function getOffset();

    /**
     * Set the Amount to use in this element.
     *
     * @param int $intAmount The amount.
     *
     * @return LimitElementInterface
     */
    public function setAmount($intAmount);

    /**
     * Get the amount to use in this element.
     *
     * @return int
     */
    public function getAmount();
}
