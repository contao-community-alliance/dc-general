<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\DCGE;
use DcGeneral\Data\ConfigInterface;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\SortElementInterface;
use DcGeneral\Panel\PanelElementInterface;

class DefaultSortElement extends AbstractElement implements SortElementInterface
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

	protected function flagToDirection($intFlag)
	{
		return ($intFlag % 2) ? 'DESC' : 'ASC';
	}

	protected function getAdditionalSorting()
	{
		$tmp            = $this->getDataContainer()->getDataDefinition()->getAdditionalSorting();
		if (!$tmp)
		{
			return array();
		}

		$arrReturn = array();
		foreach ($tmp as $strOrder)
		{
			$arrOrder = explode(' ', $strOrder);
			$strProperty  = $arrOrder[0];
			if ($this->getSelected() == $strProperty)
			{
				continue;
			}

			if (count($arrOrder) == 1)
			{
				// TODO: implicit ascending - should we rather lookup the real value from the flag?
				$arrReturn[$strProperty] = DCGE::MODEL_SORTING_ASC;
			}
			else{
				$arrReturn[$strProperty] = $arrOrder[1];
			}
		}
		return $arrReturn;
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		if (is_null($objElement))
		{
			$input = $this->getInputProvider();
			$value = null;

			if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_sort'))
			{
				$value = $input->getValue('tl_sort');
				$flag  = $this->lookupFlag($value);

				$this->setPersistent($value, (($flag%2) ? DCGE::MODEL_SORTING_ASC : DCGE::MODEL_SORTING_DESC));

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

		if (!$this->getSelected())
		{

		}

		$arrSecondOrder = $this->getAdditionalSorting();

		if (!$this->getSelected())
		{
			if ($arrSecondOrder)
			{
				$filtered = array_intersect(array_keys($arrSecondOrder), $this->getPropertyNames());

				$this->setSelected($filtered[0]);
			}

			// Still nothing selected? - use the first.
			if (!$this->getSelected())
			{
				$all = $this->getPropertyNames();
				$this->setSelected($all[0]);
			}
		}

		if (!$this->getSelected())
		{
			$current[$this->getSelected()] = $this->flagToDirection($this->getFlag());
		}

		$objConfig->setSorting($current);
	}

	/**
	 * {@inheritDoc}
	 */
	public function render($objTemplate)
	{
		foreach ($this->getPropertyNames() as $field)
		{
			$arrLabel = $this->getDataContainer()->getDataDefinition()->getProperty($field)->getLabel();

			$arrOptions[] = array(
				'value'      => specialchars($field),
				'attributes' => ($this->getSelected() == $field) ? ' selected="selected"' : '',
				'content'    => is_array($arrLabel) ? $arrLabel[0] : $arrLabel
			);
		}

		// Sort by option values
		uksort($arrOptions, 'strcasecmp');

		$objTemplate->options = $arrOptions;

		return $this;
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
		return array_keys($this->arrSorting);
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
