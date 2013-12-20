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

/**
 * Default implementation of a root condition.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
 */
class RootCondition
	extends AbstractCondition
	implements RootConditionInterface
{
	/**
	 * The filter rules to use.
	 *
	 * @var array
	 */
	protected $filter;

	/**
	 * The setter information to use when a model shall get marked as root item.
	 *
	 * @var array
	 */
	protected $setOn;

	/**
	 * The name of the table this condition is being applied to.
	 *
	 * @var string
	 */
	protected $sourceProvider;

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
	 * {@inheritdoc}
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
