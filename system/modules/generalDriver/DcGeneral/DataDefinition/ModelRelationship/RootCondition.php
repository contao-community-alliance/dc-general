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

class RootCondition
	extends AbstractCondition
	implements RootConditionInterface
{
	protected $filter;

	protected $setOn;

	/**
	 * The name of the table this condition is being applied to.
	 *
	 * @var string
	 */
	protected $sourceProvider;

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
	public function applyTo($objModel)
	{
		if ($this->setOn)
		{
			foreach ($this->setOn as $rule)
			{
				$objModel->setProperty($rule['property'], $rule['value']);
			}
		}

		return $this;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function matches($objModel)
	{
		if ($this->getFilterArray())
		{
			return $this->checkCondition($objModel, $this->getFilterArray());
		}

		return true;
	}
}
