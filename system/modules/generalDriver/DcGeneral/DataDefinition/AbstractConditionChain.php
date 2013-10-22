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
	protected $conjunction = static::AND_CONJUNCTION;

	/**
	 * Create a new condition chain.
	 *
	 * @param array  $conditions
	 * @param string $conjunction
	 */
	function __construct(array $conditions = array(), $conjunction = static::AND_CONJUNCTION)
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
		foreach ($conditions as $condition) {
			$this->addCondition($condition);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addCondition(ConditionInterface $condition)
	{
		$this->conditions[] = $condition;
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
	 */
	public function setConjunction($conjunction)
	{
		if ($conjunction != static::AND_CONJUNCTION && $conjunction != static::OR_CONJUNCTION) {
			throw new DcGeneralInvalidArgumentException('Conjunction must be ConditionChainInterface::AND_CONJUNCTION or ConditionChainInterface::OR_CONJUNCTION');
		}

		$this->conjunction = (string) $conjunction;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConjunction()
	{
		return $this->conjunction;
	}
}
