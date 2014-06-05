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
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a filter panel element.
 *
 * @package DcGeneral\Panel
 */
class DefaultFilterElement
	extends AbstractElement
	implements FilterElementInterface
{
	/**
	 * Name of the property this filter reacts on.
	 *
	 * @var string
	 */
	protected $strProperty;

	/**
	 * The current value of this filter.
	 *
	 * @var mixed
	 */
	protected $mixValue;

	/**
	 * All valid filter options of the property.
	 *
	 * @var array
	 */
	protected $arrfilterOptions;

	/**
	 * Retrieve the persistent value from the input provider.
	 *
	 * @return null|mixed
	 */
	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('filter'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('filter');
		}

		if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue))
		{
			$arrValue = $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];

			if (array_key_exists($this->getPropertyName(), $arrValue))
			{
				return $arrValue[$this->getPropertyName()];
			}
		}

		return null;
	}

	/**
	 * Store the persistent value in the input provider.
	 *
	 * @param mixed $strValue The value to store.
	 *
	 * @return void
	 */
	protected function setPersistent($strValue)
	{
		$arrValue       = array();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($this->getInputProvider()->hasPersistentValue('filter'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('filter');
		}

		if (!is_array($arrValue[$definitionName]))
		{
			$arrValue[$this->getEnvironment()->getDataDefinition()->getName()] = array();
		}

		if ((!is_null($arrValue)) && ($strValue != 'tl_' . $this->getPropertyName()))
		{
			$arrValue[$definitionName][$this->getPropertyName()] = $strValue;
		}
		else
		{
			unset($arrValue[$definitionName][$this->getPropertyName()]);
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
			$value      = $persistent;
		}

		if (!is_null($value))
		{
			$this->setValue($value);
		}

		if ($this->getPropertyName() && $this->getValue() && ($objElement !== $this))
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
			$objTempConfig = $this->getOtherConfig();
			$objTempConfig->setFields(array($this->getPropertyName()));

			$objFilterOptions = $this
				->getEnvironment()
				->getDataProvider()
				->getFilterOptions($objTempConfig);

			$arrOptions = array();
			/** @var ModelInterface $objOption */
			foreach ($objFilterOptions as $filterKey => $filterValue)
			{
				$arrOptions[$filterKey] = $filterValue;
			}

			$this->arrfilterOptions = $arrOptions;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrLabel = $this
			->getEnvironment()
			->getDataDefinition()
			->getPropertiesDefinition()
			->getProperty($this->getPropertyName())->getLabel();

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
