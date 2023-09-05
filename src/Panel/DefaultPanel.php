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
 * Default implementation of a panel row.
 */
class DefaultPanel implements PanelInterface
{
    /**
     * The panel container this panel is contained within.
     *
     * @var PanelContainerInterface
     */
    private $objContainer;

    /**
     * The elements contained within this panel.
     *
     * @var PanelElementInterface[]
     */
    private $arrElements;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->arrElements = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->objContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(PanelContainerInterface $container)
    {
        $this->objContainer = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addElement($panelName, $element)
    {
        $this->arrElements[$panelName] = $element;
        $element->setPanel($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getElement($elementName)
    {
        return ($this->arrElements[$elementName] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null)
    {
        /** @var PanelElementInterface $currentElement */
        foreach ($this as $currentElement) {
            $currentElement->initialize($config, $element);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arrElements);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->arrElements);
    }
}
