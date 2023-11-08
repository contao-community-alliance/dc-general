<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;

/**
 * Interface BackendViewInterface.
 *
 * This interface describes extensions over the ViewInterface regarding the Contao 2 Backend view.
 */
interface BackendViewInterface extends ViewInterface
{
    /**
     * Set the panel container.
     *
     * @param PanelContainerInterface $panelContainer The panel container.
     *
     * @return BackendViewInterface
     */
    public function setPanel($panelContainer);

    /**
     * Retrieve the panel container from the view.
     *
     * @return PanelContainerInterface|null
     */
    public function getPanel();
}
