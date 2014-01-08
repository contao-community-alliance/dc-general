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

namespace DcGeneral\DataDefinition;

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * An abstract condition chain.
 *
 * @package DcGeneral\DataDefinition
 */
abstract class AbstractConditionChain implements ConditionChainInterface
{
	/**
	 * The list of conditions.
	 *
	 * @var ConditionInterface[]
	 */
	protected $conditions = array();

	/**
	 * The conjunction mode.
	 *
	 * @var string
	 */
	protected $conjunction = self::AND_CONJUNCTION;

	/**
	 * Create a new condition chain.
	 *
	 * @param array  $conditions  The conditions to initialize the chain with (optional).
	 *
	 * @param string $conjunction The conjunction this chain contains (defaults to AND).
	 */
	public function __construct(array $conditions = array(), $conjunction = self::AND_CONJUNCTION)
	{
		$this->addConditions($conditions);
		$this->setConjunction($conjunction);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearConditions()
	{
		$this->conditions = array();
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConditions(array $conditions)
	{
		$this->clearConditions();
		$this->addConditions($conditions);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addConditions(array $conditions)
	{
		foreach ($conditions as $condition)
		{
			$this->addCondition($condition);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addCondition(ConditionInterface $condition)
	{
		$hash = spl_object_hash($condition);

		$this->conditions[$hash] = $condition;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeCondition(ConditionInterface $condition)
	{
		$hash = spl_object_hash($condition);
		unset($this->conditions[$hash]);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConditions()
	{
		return array_values($this->conditions);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException When the conjunction is neither AND nor OR.
	 */
	public function setConjunction($conjunction)
	{
		if ($conjunction != static::AND_CONJUNCTION && $conjunction != static::OR_CONJUNCTION)
		{
			throw new DcGeneralInvalidArgumentException(
				'Conjunction must be ConditionChainInterface::AND_CONJUNCTION or ConditionChainInterface::OR_CONJUNCTION'
			);
		}

		$this->conjunction = (string)$conjunction;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConjunction()
	{
		return $this->conjunction;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$conditions = array();
		foreach ($conditions as $index => $condition)
		{
			$conditions[$index] = clone $condition;
		}
		$this->conditions = $conditions;
	}
}
