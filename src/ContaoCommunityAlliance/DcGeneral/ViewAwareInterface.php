<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;

/**
 * Base interface providing access to a view.
 *
 * @package DcGeneral
 */
interface ViewAwareInterface
{
    /**
     * Return the view.
     *
     * @return ViewInterface
     */
    public function getView();
}
