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
use DcGeneral\Data\ModelInterface;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\PanelElementInterface;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\View\ViewTemplateInterface;

class DefaultFilterElement extends AbstractElement implements FilterElementInterface
{
	/**
	 * @var string
	 */
	protected $strProperty;

	/**
	 * @var mixed
	 */
	protected $mixValue;

	protected $arrfilterOptions;


	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('filter'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('filter');
		}

		if (array_key_exists($this->getDataContainer()->getName(), $arrValue))
		{
			$arrValue = $arrValue[$this->getDataContainer()->getName()];

			if (array_key_exists($this->getPropertyName(), $arrValue))
			{
				return $arrValue[$this->getPropertyName()];
			}
		}

		return null;
	}

	protected function setPersistent($strValue)
	{
		$arrValue = array();

		if ($this->getInputProvider()->hasPersistentValue('filter'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('filter');
		}

		if (!is_array($arrValue[$this->getDataContainer()->getName()]))
		{
			$arrValue[$this->getDataContainer()->getName()] = array();
		}

		if ((!is_null($arrValue)) && ($strValue != 'tl_' . $this->getPropertyName()))
		{
			$arrValue[$this->getDataContainer()->getName()][$this->getPropertyName()] = $strValue;
		}
		else
		{
			unset($arrValue[$this->getDataContainer()->getName()][$this->getPropertyName()]);
		}

		$this->getInputProvider()->setPersistentValue('filter', $arrValue);
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		$input = $this->getInputProvider();
		$value = null;

		if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue($this->getPropertyName()))
		{
			$value = $input->getValue($this->getPropertyName());

			$this->setPersistent($value);
		}

		if ($input->hasPersistentValue('filter'))
		{
			$persistent = $this->getPersistent();
			$value = $persistent;
		}

		if (!is_null($value))
		{
			$this->setValue($value);
		}

		if ($this->getPropertyName() && $this->getValue())
		{
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
							'operation' => '=',
							'property' => $this->getPropertyName(),
							'value' => $this->getValue()
						))
					)
				)
			));
		}

		// Finally load the filter options.
		if (is_null($objElement))
		{
			$objTempConfig = $this->getOtherConfig($objConfig);
			$objTempConfig->setFields(array($this->getPropertyName()));

			$objFilterOptions = $this
				->getPanel()
				->getContainer()
				->getDataContainer()
				->getDataProvider()
				->getFilterOptions($objTempConfig);

			$arrOptions = array();
			/**
			 * @var ModelInterface $objOption
			 */
			foreach ($objFilterOptions as $objOption)
			{
				$optionKey = $optionValue = $objOption->getProperty($this->getPropertyName());

				if ($optionValue instanceof \DateTime) {
					$optionKey = $optionValue->getTimestamp();
					$optionValue = $optionValue->format($GLOBALS['TL_CONFIG']['dateFormat']);
				}

				$arrOptions[$optionKey] = $optionValue;
			}
			$this->arrfilterOptions = $arrOptions;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrLabel = $this->getDataContainer()->getDataDefinition()->getProperty($this->getPropertyName())->getName();

		$arrOptions = array(
			array(
				'value'   => 'tl_' . $this->getPropertyName(),
				'content' => (is_array($arrLabel) ? $arrLabel[0] : $arrLabel)
			),
			array(
				'value'   => 'tl_' . $this->getPropertyName(),
				'content' => '---'
			)
		);

		foreach ($this->arrfilterOptions as $key => $value)
		{
			$arrOptions[] = array
			(
				'value'      => $key,
				'content'    => $value,
				'attributes' => ($key === $this->getValue()) ? ' selected="selected"' : ''
			);
		}

		$objTemplate->name    = $this->getPropertyName();
		$objTemplate->id      = $this->getPropertyName();
		$objTemplate->class   = 'tl_select' . (!is_null($this->getValue()) ? ' active' : '');
		$objTemplate->options = $arrOptions;
		$objTemplate->active  = $this->getValue();

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPropertyName($strProperty)
	{
		$this->strProperty = $strProperty;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyName()
	{
		return $this->strProperty;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($mixValue)
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
