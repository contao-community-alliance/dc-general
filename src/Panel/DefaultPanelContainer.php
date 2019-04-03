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

/**
 * Default implementation of a panel container.
 */
class DefaultPanelContainer implements PanelContainerInterface
{
    /**
     * The environment in use.
     *
     * @var EnvironmentInterface
     */
    private $objEnvironment;

    /**
     * The panels contained within this container.
     *
     * @var PanelInterface[]
     */
    private $arrPanels = [];

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->objEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->objEnvironment = $environment;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPanel($panelName, $panel)
    {
        $this->arrPanels[$panelName] = $panel;
        $panel->setContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel($panelName)
    {
        return $this->arrPanels[$panelName];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null)
    {
        /** @var PanelInterface $panel */
        foreach ($this as $panel) {
            $panel->initialize($config, $element);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateValues()
    {
        return ('tl_filters' === $this->getEnvironment()->getInputProvider()->getValue('FORM_SUBMIT'));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arrPanels);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->arrPanels);
    }
}
