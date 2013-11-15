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
 * Condition for the default palette.
 */
class NotCondition implements PropertyConditionInterface
{
	/**
	 * The condition to negate.
	 *
	 * @var PropertyConditionInterface
	 */
	protected $condition;

	function __construct(PropertyConditionInterface $condition)
	{
		$this->condition = $condition;
	}

	/**
	 * @param PropertyConditionInterface $condition
	 */
	public function setCondition(PropertyConditionInterface $condition)
	{
		$this->condition = $condition;
		return $this;
	}

	/**
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
