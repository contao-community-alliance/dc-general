<?php
/**
 * PHP version 5
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

/**
 * Default implementation of a panel row.
 *
 * @package DcGeneral\Panel
 */
class DefaultPanel
	implements PanelInterface
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
		return $this->arrElements[$strKey];
	}

	/**
	 * {@inheritdoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		/** @var PanelElementInterface $objThisElement */
		foreach ($this as $objThisElement)
		{
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
