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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;

/**
 * Abstract base implementation for panel elements.
 */
abstract class AbstractElement implements PanelElementInterface
{

    /**
     * The panel this element is contained within.
     *
     * @var PanelInterface
     */
    protected $objPanel;

    /**
     * The base configuration that contains all filter, sorting and limit information for all other panel elements.
     *
     * This is used for determining the valid values in filters etc.
     *
     * @var ConfigInterface
     */
    private $objOtherConfig;

    /**
     * Convenience method to retrieve Environment for this element.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->getPanel()->getContainer()->getEnvironment();
    }

    /**
     * Convenience method to retrieve session storage for this Element.
     *
     * @return SessionStorageInterface
     */
    public function getSessionStorage()
    {
        return $this->getEnvironment()->getSessionStorage();
    }

    /**
     * Convenience method to retrieve input provider for this Element.
     *
     * @return InputProviderInterface
     */
    public function getInputProvider()
    {
        return $this->getEnvironment()->getInputProvider();
    }

    /**
     * {@inheritDoc}
     */
    public function getPanel()
    {
        return $this->objPanel;
    }

    /**
     * {@inheritDoc}
     */
    public function setPanel(PanelInterface $panel)
    {
        $this->objPanel = $panel;

        return $this;
    }

    /**
     * Let all other elements initialize and apply themselves to this config.
     *
     * @return ConfigInterface
     */
    protected function getOtherConfig()
    {
        if (!isset($this->objOtherConfig)) {
            $this->objOtherConfig = $this
                ->getEnvironment()
                ->getBaseConfigRegistry()
                ->getBaseConfig();

            $this
                ->getPanel()
                ->getContainer()
                ->initialize($this->objOtherConfig, $this);
        }

        return $this->objOtherConfig;
    }
}
