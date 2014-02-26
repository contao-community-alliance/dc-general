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

use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a parent child relationship.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
 */
class ParentChildCondition
	extends AbstractCondition
	implements ParentChildConditionInterface
{
	/**
	 * The filter rules.
	 *
	 * @var array
	 */
	protected $filter;

	/**
	 * The filter rules to use for an inverse filter.
	 *
	 * @var array
	 */
	protected $inverseFilter;

	/**
	 * The values to use when enforcing a root condition.
	 *
	 * @var array
	 */
	protected $setOn;

	/**
	 * The name of the source provider (parent).
	 *
	 * @var string
	 */
	protected $sourceProvider;

	/**
	 * The name of the destination provider (child).
	 *
	 * @var string
	 */
	protected $destinationProvider;

	/**
	 * {@inheritdoc}
	 */
	public function setFilterArray($value)
	{
		$this->filter = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterArray()
	{
		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSetters($value)
	{
		$this->setOn = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSetters()
	{
		return $this->setOn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInverseFilterArray($value)
	{
		$this->inverseFilter = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInverseFilterArray()
	{
		return $this->inverseFilter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSourceName($value)
	{
		$this->sourceProvider = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSourceName()
	{
		return $this->sourceProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDestinationName($value)
	{
		$this->destinationProvider = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDestinationName()
	{
		return $this->destinationProvider;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException when an empty parent model is given.
	 */
	public function getFilter($objParent)
	{
		if (!$objParent)
		{
			throw new DcGeneralInvalidArgumentException('No parent model passed.');
		}

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
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException As this is currently unimplemented.
	 */
	public function applyTo($objParent, $objChild)
	{
		$setters = $this->getSetters();

		if (empty($setters) || !is_array($setters))
		{
			throw new DcGeneralRuntimeException(sprintf(
				'No relationship setter defined from %s to %s.',
				$this->getSourceName(),
				$this->getDestinationName()
			));
		}

		foreach ($setters as $setter)
		{
			if (!(is_array($setter)
				&& (count($setter) == 2)
				&& isset($setter['to_field'])
				&& (isset($setter['from_field']) || isset($setter['value']))
			))
			{
				throw new DcGeneralRuntimeException(sprintf(
					'Invalid relationship setter entry: %s',
					var_export($setter, true)
				));
			}

			if (isset($setter['from_field']))
			{
				$objChild->setProperty($setter['to_field'], $objParent->getProperty($setter['from_field']));
			}
			else
			{
				$objChild->setProperty($setter['to_field'], $setter['value']);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function copyFrom($sourceModel, $destinationModel)
	{
		$setters = $this->getSetters();

		if (empty($setters) || !is_array($setters))
		{
			throw new DcGeneralRuntimeException(sprintf(
				'No relationship setter defined from %s to %s.',
				$this->getSourceName(),
				$this->getDestinationName()
			));
		}

		foreach ($setters as $setter)
		{
			if (!(is_array($setter)
				&& (count($setter) == 2)
				&& isset($setter['to_field'])
				&& (isset($setter['from_field']) || isset($setter['value']))
			))
			{
				throw new DcGeneralRuntimeException(sprintf(
					'Invalid relationship setter entry: %s',
					var_export($setter, true)
				));
			}

			if (isset($setter['from_field']))
			{
				$destinationModel->setProperty($setter['to_field'], $sourceModel->getProperty($setter['to_field']));
			}
			else
			{
				$destinationModel->setProperty($setter['to_field'], $setter['value']);
			}
		}
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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




