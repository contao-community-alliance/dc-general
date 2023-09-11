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

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
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
     * @var PanelInterface|null
     */
    protected $objPanel = null;

    /**
     * The base configuration that contains all filter, sorting and limit information for all other panel elements.
     *
     * This is used for determining the valid values in filters etc.
     *
     * @var ConfigInterface|null
     */
    private $objOtherConfig = null;

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
        $sessionStorage = $this->getEnvironment()->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        return $sessionStorage;
    }

    /**
     * Convenience method to retrieve input provider for this Element.
     *
     * @return InputProviderInterface
     */
    public function getInputProvider()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return $inputProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getPanel()
    {
        if (null === $this->objPanel) {
            throw new \LogicException('Panel not set');
        }

        return $this->objPanel;
    }

    /**
     * {@inheritDoc}
     */
    public function setPanel(PanelInterface $panelElement)
    {
        $this->objPanel = $panelElement;

        return $this;
    }

    /**
     * Let all other elements initialize and apply themselves to this config.
     *
     * @return ConfigInterface
     */
    protected function getOtherConfig()
    {
        if (null === $this->objOtherConfig) {
            $registry = $this->getEnvironment()->getBaseConfigRegistry();
            assert($registry instanceof BaseConfigRegistryInterface);

            $this->objOtherConfig = $registry->getBaseConfig();

            $this
                ->getPanel()
                ->getContainer()
                ->initialize($this->objOtherConfig, $this);
        }

        return $this->objOtherConfig;
    }
}
