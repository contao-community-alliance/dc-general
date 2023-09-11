<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * A simple element contained within a panel.
 */
interface PanelElementInterface
{
    /**
     * Return the parenting panel.
     *
     * @return PanelInterface
     */
    public function getPanel();

    /**
     * Return the parenting panel.
     *
     * @param PanelInterface $panelElement The panel to use as parent.
     *
     * @return PanelElementInterface
     */
    public function setPanel(PanelInterface $panelElement);

    /**
     * Initialize the passed configuration with the values of the element.
     *
     * @param ConfigInterface            $config  The config to which the initialization shall be applied to.
     * @param PanelElementInterface|null $element The element to be initialized (if any).
     *
     * @return void
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null);

    /**
     * Render the element using the given Template.
     *
     * @param ViewTemplateInterface $viewTemplate The Template to use.
     *
     * @return self
     */
    public function render(ViewTemplateInterface $viewTemplate);
}
