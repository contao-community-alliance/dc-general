<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
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
    protected $objEnvironment;

    /**
     * The panels contained within this container.
     *
     * @var PanelInterface[]
     */
    protected $arrPanels = array();

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
    public function setEnvironment(EnvironmentInterface $objEnvironment)
    {
        $this->objEnvironment = $objEnvironment;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPanel($strKey, $objPanel)
    {
        $this->arrPanels[$strKey] = $objPanel;
        $objPanel->setContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel($strKey)
    {
        return $this->arrPanels[$strKey];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
    {
        /** @var PanelInterface $objPanel */

        foreach ($this as $objPanel) {
            $objPanel->initialize($objConfig, $objElement);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateValues()
    {
        return ($this->getEnvironment()->getInputProvider()->getValue('FORM_SUBMIT') === 'tl_filters');
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
        return count($this->arrPanels);
    }
}
