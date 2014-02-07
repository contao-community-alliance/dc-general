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

namespace DcGeneral\DataDefinition\Palette\Condition\Property;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;

/**
 * Negate a condition.
 */
class NotCondition implements PropertyConditionInterface
{
	/**
	 * The condition to negate.
	 *
	 * @var PropertyConditionInterface
	 */
	protected $condition;

	/**
	 * Create a new instance.
	 *
	 * @param PropertyConditionInterface $condition The condition to negate.
	 */
	public function __construct(PropertyConditionInterface $condition)
	{
		$this->condition = $condition;
	}

	/**
	 * Set the condition to negate.
	 *
	 * @param PropertyConditionInterface $condition The condition.
	 *
	 * @return NotCondition
	 */
	public function setCondition(PropertyConditionInterface $condition)
	{
		$this->condition = $condition;
		return $this;
	}

	/**
	 * Retrieve the condition to negate.
	 *
	 * @return PropertyConditionInterface
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		return !$this->condition->match($model, $input);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$this->condition = clone $this->condition;
	}
}
