<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\PanelElementInterface;
use DcGeneral\Panel\SearchElementInterface;
use DcGeneral\View\ViewTemplateInterface;

class DefaultSearchElement extends AbstractElement implements SearchElementInterface
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

		if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue))
		{
			return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
		}

		return array();
	}

	protected function setPersistent($strProperty, $strValue)
	{
		$arrValue       = array();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($this->getInputProvider()->hasPersistentValue('search'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('search');
		}

		if (!empty($strValue))
		{
			if (!is_array($arrValue[$definitionName]))
			{
				$arrValue[$definitionName] = array();
			}

			if ($strValue)
			{
				$arrValue[$definitionName]['field'] = $strProperty;
				$arrValue[$definitionName]['value'] = $strValue;
			}
			else
			{
				unset($arrValue[$definitionName]);
			}
		}
		else
		{
			unset($arrValue[$definitionName]);
		}

		$this->getInputProvider()->setPersistentValue('search', $arrValue);
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
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
		elseif ($input->hasPersistentValue('search'))
		{
			$persistent = $this->getPersistent();
			if ($persistent)
			{
				$field = $persistent['field'];
				$value = $persistent['value'];
			}
		}

		$this->setSelectedProperty($field);
		$this->setValue($value);

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
					'operation' => 'AND',
					'children' => array(array(
						'operation' => 'LIKE',
						'property' => $this->getSelectedProperty(),
						'value' => sprintf('*%s*', $this->getValue())
					))
				)
			)
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrOptions = array();

		foreach ($this->getPropertyNames() as $field)
		{
			$arrLabel     = $this->getEnvironment()->getDataDefinition()->getPropertiesSection()->getProperty($field)->getLabel();
			$arrOptions[] = array
			(
				'value'      => $field,
				'content'    => is_array($arrLabel) ? $arrLabel[0] : $arrLabel,
				'attributes' => ($field == $this->getSelectedProperty()) ? ' selected="selected"' : ''
			);
		}

		$objTemplate->class   = 'tl_select' . (!is_null($this->getValue()) ? ' active' : '');
		$objTemplate->options = $arrOptions;
		$objTemplate->value  = $this->getValue();

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($strProperty)
	{

		$this->arrProperties[] = $strProperty;

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
