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

namespace DcGeneral\DataDefinition\Palette;

use DcGeneral\DataDefinition\PropertyInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * A condition define when a property is visible or editable and when not.
 */
class PropertyConditionChain implements PropertyConditionInterface
{
	/**
	 * All conditions must match.
	 */
	const AND_CONJUNCTION = 'AND';

	/**
	 * Only one condition must match.
	 */
	const OR_CONJUNCTION = 'OR';

	/**
	 * The list of conditions.
	 *
	 * @var PropertyConditionInterface[]
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
	 * Clear the chain.
	 *
	 * @return ChainCondition
	 */
	public function clearConditions()
	{
		$this->conditions = array();
		return $this;
	}

	/**
	 * Set the conditions in this chain.
	 *
	 * @return ChainCondition
	 */
	public function setConditions(array $conditions)
	{
		$this->clearConditions();
		$this->addConditions($conditions);
		return $this;
	}

	/**
	 * Add multiple conditions to this chain.
	 *
	 * @param PropertyConditionInterface[] $conditions
	 *
	 * @return ChainCondition
	 */
	public function addConditions(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->addCondition($condition);
		}
		return $this;
	}

	/**
	 * Add a condition to this chain.
	 *
	 * @param PropertyConditionInterface[] $conditions
	 *
	 * @return ChainCondition
	 */
	public function addCondition(PropertyConditionInterface $condition)
	{
		$this->conditions[] = $condition;
		return $this;
	}

	/**
	 * Remove a condition from this chain.
	 *
	 * @param PropertyConditionInterface[] $conditions
	 *
	 * @return ChainCondition
	 */
	public function removeCondition(PropertyConditionInterface $condition)
	{
		$hash = spl_object_hash($condition);
		unset($this->conditions[$hash]);
		return $this;
	}

	/**
	 * @return PropertyConditionInterface[]
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * @param string $conjunction
	 */
	public function setConjunction($conjunction)
	{
		if ($conjunction != static::AND_CONJUNCTION && $conjunction != static::OR_CONJUNCTION) {
			throw new DcGeneralInvalidArgumentException('Conjunction must be PropertyConditionChain::AND_CONJUNCTION or PropertyConditionChain::OR_CONJUNCTION');
		}

		$this->conjunction = (string) $conjunction;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getConjunction()
	{
		return $this->conjunction;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->conjunction == static::AND_CONJUNCTION) {
			foreach ($this->conditions as $condition) {
				if (!$condition->isVisible($model, $input)) {
					return false;
				}
			}

			return true;
		}
		else {
			foreach ($this->conditions as $condition) {
				if ($condition->isVisible($model, $input)) {
					return true;
				}
			}

			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditable(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->conjunction == static::AND_CONJUNCTION) {
			foreach ($this->conditions as $condition) {
				if (!$condition->isEditable($model, $input)) {
					return false;
				}
			}

			return true;
		}
		else {
			foreach ($this->conditions as $condition) {
				if ($condition->isEditable($model, $input)) {
					return true;
				}
			}

			return false;
		}
	}
}
