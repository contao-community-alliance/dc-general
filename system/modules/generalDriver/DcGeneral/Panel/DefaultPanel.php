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

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\Panel\PanelContainerInterface;
use DcGeneral\Panel\PanelElementInterface;
use DcGeneral\Panel\PanelInterface;

class DefaultPanel implements PanelInterface
{
	/**
	 * @var PanelContainerInterface
	 */
	protected $objContainer;

	/**
	 * @var PanelElementInterface[]
	 */
	protected $arrElements;


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
}
