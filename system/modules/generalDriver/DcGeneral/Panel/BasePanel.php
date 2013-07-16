<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\Interfaces\Config;
use DcGeneral\Panel\Interfaces\Container;
use DcGeneral\Panel\Interfaces\Element;
use DcGeneral\Panel\Interfaces\Panel;

class BasePanel implements Panel
{
	/**
	 * @var Container
	 */
	protected $objContainer;

	/**
	 * @var Element[]
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
	public function setContainer(Container $objContainer)
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
	public function initialize(Config $objConfig, Element $objElement = null)
	{
		/** @var Element $objThisElement */
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
