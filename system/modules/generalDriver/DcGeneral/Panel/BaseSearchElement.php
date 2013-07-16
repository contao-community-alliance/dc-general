<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\Interfaces\Config;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\Interfaces\Element;
use DcGeneral\Panel\Interfaces\SearchElement;

class BaseSearchElement extends AbstractElement implements SearchElement
{
	/**
	 * The properties to be allowed to be searched on.
	 *
	 * @var array
	 */
	protected $arrProperties;

	/**
	 * The currently active property to be searched on.
	 *
	 * @var string
	 */
	protected $strSelectedProperty;

	/**
	 * @var mixed
	 */
	protected $mixValue;

	/**
	 * The filter options available.
	 *
	 * @var
	 */
	protected $arrfilterOptions;

	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('search'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('search');
		}

		if (array_key_exists($this->getDataContainer()->getName(), $arrValue))
		{
			return $arrValue[$this->getDataContainer()->getName()];
		}

		return array();
	}

	protected function setPersistent($strProperty, $strValue)
	{
		$arrValue = array();

		if ($this->getInputProvider()->hasPersistentValue('search'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('search');
		}

		if ($strValue)
		{
			if (!is_array($arrValue[$this->getDataContainer()->getName()]))
			{
				$arrValue[$this->getDataContainer()->getName()] = array();
			}

			$arrValue[$this->getDataContainer()->getName()]['field'] = $strProperty;
			$arrValue[$this->getDataContainer()->getName()]['value'] = $strValue;
		}
		else
		{
			unset($arrValue[$this->getDataContainer()->getName()]);
		}

		$this->getInputProvider()->setPersistentValue('search', $arrValue);
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(Config $objConfig, Element $objElement = null)
	{
		$input = $this->getInputProvider();
		$value = null;
		$field = null;

		if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_field'))
		{
			$field = $input->getValue('tl_field');
			$value = $input->getValue('tl_value');

			$this->setPersistent($field, $value);
		}
		elseif ($input->hasPersistentValue('tl_sort'))
		{
			$persistent = $this->getPersistent();
			if ($persistent)
			{
				$field = $persistent['field'];
				$value = $persistent['value'];
			}
		}

		if (!is_null($value))
		{
			$this->setSelectedProperty($field);
			$this->setValue($value);
		}

		if (!($this->getSelectedProperty() && $this->getValue()))
		{
			return;
		}

		$arrCurrent = $objConfig->getFilter();
		if (!is_array($arrCurrent))
		{
			$arrCurrent = array();
		}

		$objConfig->setFilter(array_merge_recursive(
			$arrCurrent,
			array(
				array(
/*
					'operation' => 'AND',
					// FIXME: change childs to children
					'childs' => array(array(
*/
						'operation' => 'LIKE',
						'property' => $this->getSelectedProperty(),
						'value' => '*' . $this->getValue() . '*'
/*
					))
*/
				)
			)
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function render($objTemplate)
	{
		// TODO: unimplemented.

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($strProperty, $intFlag = 0)
	{

		$this->arrProperties[$strProperty] = $intFlag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return $this->arrProperties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSelectedProperty($strProperty = '')
	{
		$this->strSelectedProperty = $strProperty;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSelectedProperty()
	{
		return $this->strSelectedProperty;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($mixValue = null)
	{
		$this->mixValue = $mixValue;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		return $this->mixValue;
	}
}
