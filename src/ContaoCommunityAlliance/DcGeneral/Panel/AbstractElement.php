<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Abstract base implementation for panel elements.
 *
 * @package DcGeneral\Panel
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
    public function setPanel(PanelInterface $objPanel)
    {
        $this->objPanel = $objPanel;

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
                ->getController()
                ->getBaseConfig();

            $this
                ->getPanel()
                ->getContainer()
                ->initialize($this->objOtherConfig, $this);
        }

        return $this->objOtherConfig;
    }
}
