<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\Interfaces\Config;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\Interfaces\SortElement;
use DcGeneral\Panel\Interfaces\Element;

class BaseSortElement extends AbstractElement implements SortElement
{
	/**
	 * The default flag to use.
	 *
	 * @var int
	 */
	public $intDefaultFlag;

	/**
	 * @var array
	 */
	protected $arrSorting;

	/**
	 * @var mixed
	 */
	protected $strSelected;

	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if (array_key_exists($this->getDataContainer()->getName(), $arrValue))
		{
			return $arrValue[$this->getDataContainer()->getName()];
		}

		return array();
	}

	protected function setPersistent($strProperty)
	{
		$arrValue = array();

		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if ($strProperty)
		{
			if (!is_array($arrValue[$this->getDataContainer()->getName()]))
			{
				$arrValue[$this->getDataContainer()->getName()] = array();
			}
			$arrValue[$this->getDataContainer()->getName()] = $strProperty;
		}
		else
		{
			unset($arrValue[$this->getDataContainer()->getName()]);
		}

		$this->getInputProvider()->setPersistentValue('sorting', $arrValue);
	}

	protected function lookupFlag($strProperty)
	{
		return ($this->arrSorting[$strProperty])
			? $this->arrSorting[$strProperty]
			: $this->getDefaultFlag();
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(Config $objConfig, Element $objElement = null)
	{
		if (is_null($objElement))
		{
			$input = $this->getInputProvider();
			$value = null;

			if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_sort'))
			{
				$value = $input->getValue('tl_sort');
				$flag  = $this->lookupFlag($value);

				$this->setPersistent($value, (($flag%2) ? 'ASC' : 'DESC'));

				$this->setSelected($this->getPersistent());
			}
			else
			{
				$this->setSelected($this->getPersistent());
			}
		}

		if (!($this->getSelected() && ($this->getFlag() !== null)))
		{
			return;
		}

		$current = $objConfig->getSorting();

		if (!is_array($current))
		{
			$current = array();
		}

		$direction = in_array($this->getFlag(), array(2, 4, 6, 8, 10, 12)) ? 'DESC' : 'ASC';

		$objConfig->setSorting(array_merge($current, array($this->getSelected() => $direction)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function render($objTemplate)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultFlag($intFlag)
	{
		$this->intDefaultFlag = $intFlag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultFlag()
	{
		return $this->intDefaultFlag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($strPropertyName, $intFlag)
	{
		$this->arrSorting[$strPropertyName] = $intFlag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return $this->arrSorting;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSelected($strPropertyName)
	{
		$this->strSelected = $strPropertyName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSelected()
	{
		return $this->strSelected;
	}

	public function getFlag()
	{
		return $this->lookupFlag($this->getSelected());
	}
}
