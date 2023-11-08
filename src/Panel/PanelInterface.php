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

/**
 * This interface describes a panel.
 *
 * A panel is a row of a panel container.
 *
 * @extends \IteratorAggregate<string, PanelElementInterface>
 */
interface PanelInterface extends \IteratorAggregate, \Countable
{
    /**
     * Get the parenting container.
     *
     * @return PanelContainerInterface
     */
    public function getContainer();

    /**
     * Set the parenting container.
     *
     * @param PanelContainerInterface $container The Container to be used as parent.
     *
     * @return PanelInterface
     */
    public function setContainer(PanelContainerInterface $container);

    /**
     * Add an element to the panel.
     *
     * @param string                $panelName Name of the panel.
     * @param PanelElementInterface $element   The element instance to add.
     *
     * @return mixed
     */
    public function addElement($panelName, $element);

    /**
     * Retrieve an element with the given name.
     *
     * @param string $elementName The name of the element.
     *
     * @return PanelElementInterface|null
     */
    public function getElement($elementName);

    /**
     * Initialize the passed config via all contained elements.
     *
     * @param ConfigInterface            $config  The config to which the initialization shall be applied to.
     * @param PanelElementInterface|null $element The element to be initialized (if any).
     *
     * @return void
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null);
}
