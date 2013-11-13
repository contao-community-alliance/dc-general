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

namespace DcGeneral\DataDefinition\ModelRelationship;

class ParentChildCondition
	extends AbstractCondition
	implements ParentChildConditionInterface
{
	protected $filter;

	protected $inverseFilter;

	protected $setOn;

	protected $sourceProvider;

	protected $destinationProvider;

	/**
	 * {@inheritedDoc}
	 */
	public function setFilterArray($value)
	{
		$this->filter = $value;

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilterArray()
	{
		return $this->filter;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function setSetters($value)
	{
		$this->setOn = $value;

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getSetters()
	{
		return $this->setOn;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function setInverseFilterArray($value)
	{
		$this->inverseFilter = $value;

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getInverseFilterArray()
	{
		return $this->inverseFilter;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function setSourceName($value)
	{
		$this->sourceProvider = $value;

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getSourceName()
	{
		return $this->sourceProvider;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function setDestinationName($value)
	{
		$this->destinationProvider = $value;

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getDestinationName()
	{
		return $this->destinationProvider;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilter($objParent)
	{
		$arrResult = array();
		foreach ($this->getFilterArray() as $arrRule)
		{
			$arrApplied = array(
				'operation'   => $arrRule['operation'],
			);

			if (isset($arrRule['local']))
			{
				$arrApplied['property'] = $arrRule['local'];
			}

			if (isset($arrRule['remote']))
			{
				$arrApplied['value'] = $objParent->getProperty($arrRule['remote']);
			}

			if (isset($arrRule['remote_value']))
			{
				$arrApplied['value'] = $arrRule['remote_value'];
			}

			if (isset($arrRule['value']))
			{
				$arrApplied['value'] = $arrRule['value'];
			}

			$arrResult[] = $arrApplied;
		}

		return $arrResult;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function applyTo($objParent, $objChild)
	{


		// FIXME: unimplemented.
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getInverseFilterFor($objChild)
	{
		$arrResult = array();
		foreach ($this->getInverseFilterArray() as $arrRule)
		{
			$arrApplied = array(
				'operation'   => $arrRule['operation'],
			);

			if (isset($arrRule['remote']))
			{
				$arrApplied['property'] = $arrRule['remote'];
			}

			if (isset($arrRule['local']))
			{
				$arrApplied['value'] = $objChild->getProperty($arrRule['local']);
			}

			if (isset($arrRule['value']))
			{
				$arrApplied['value'] = $arrRule['value'];
			}

			$arrResult[] = $arrApplied;
		}

		return $arrResult;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function matches($objParent, $objChild)
	{
		$filter = array();
		foreach ($this->getFilterArray() as $arrRule)
		{
			$arrApplied = array(
				'operation'   => $arrRule['operation'],
			);

			if (isset($arrRule['local']))
			{
				$arrApplied['property'] = $objChild->getProperty($arrRule['local']);
			}

			if (isset($arrRule['remote']))
			{
				$arrApplied['value'] = $arrRule['remote'];
			}

			if (isset($arrRule['remote_value']))
			{
				$arrApplied['value'] = $arrRule['remote_value'];
			}

			if (isset($arrRule['value']))
			{
				$arrApplied['value'] = $arrRule['value'];
			}

			$filter[] = $arrApplied;
		}

		return $this->checkCondition($objParent, $filter);
	}
}




