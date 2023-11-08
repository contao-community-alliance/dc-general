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
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Default implementation of a panel container.
 */
class DefaultPanelContainer implements PanelContainerInterface
{
    /**
     * The environment in use.
     *
     * @var EnvironmentInterface|null
     */
    private $objEnvironment = null;

    /**
     * The panels contained within this container.
     *
     * @var array<string, PanelInterface>
     */
    private array $panels = [];

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        if (null === $this->objEnvironment) {
            throw new \LogicException('Environment for panel is not set.');
        }

        return $this->objEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(EnvironmentInterface $objEnvironment)
    {
        $this->objEnvironment = $objEnvironment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPanel($panelName, $panel)
    {
        $this->panels[$panelName] = $panel;
        $panel->setContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel($panelName)
    {
        return $this->panels[$panelName];
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

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updateValues()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return ('tl_filters' === $inputProvider->getValue('FORM_SUBMIT'));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->panels);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->panels);
    }
}
