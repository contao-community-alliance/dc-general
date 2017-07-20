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

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;

/**
 * This interface describes a panel.
 *
 * A panel is a row of a panel container.
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
     * @param PanelContainerInterface $objContainer The Container to be used as parent.
     *
     * @return PanelInterface
     */
    public function setContainer(PanelContainerInterface $objContainer);

    /**
     * Add an element to the panel.
     *
     * @param string                $strKey     Name of the panel.
     *
     * @param PanelElementInterface $objElement The element instance to add.
     *
     * @return mixed
     */
    public function addElement($strKey, $objElement);

    /**
     * Retrieve an element with the given name.
     *
     * @param string $strKey The name of the element.
     *
     * @return PanelElementInterface
     */
    public function getElement($strKey);

    /**
     * Initialize the passed config via all contained elements.
     *
     * @param ConfigInterface       $objConfig  The config to which the initialization shall be applied to.
     *
     * @param PanelElementInterface $objElement The element to be initialized (if any).
     *
     * @return void
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);
}
