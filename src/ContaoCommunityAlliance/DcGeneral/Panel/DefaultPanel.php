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
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
    protected $objContainer;

    /**
     * The elements contained within this panel.
     *
     * @var PanelElementInterface[]
     */
    protected $arrElements;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->arrElements = array();
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
    public function setContainer(PanelContainerInterface $objContainer)
    {
        $this->objContainer = $objContainer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addElement($strKey, $objElement)
    {
        $this->arrElements[$strKey] = $objElement;
        $objElement->setPanel($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getElement($strKey)
    {
        return isset($this->arrElements[$strKey]) ? $this->arrElements[$strKey] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
    {
        /** @var PanelElementInterface $objThisElement */
        foreach ($this as $objThisElement) {
            $objThisElement->initialize($objConfig, $objElement);
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
    public function count()
    {
        return count($this->arrElements);
    }
}
