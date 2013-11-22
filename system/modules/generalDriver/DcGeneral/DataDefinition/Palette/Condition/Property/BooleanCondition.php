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
 * Condition for specifying an explicit boolean value (Useful for determining if a property shall be editable i.e.).
 */
class BooleanCondition implements PropertyConditionInterface
{
	/**
	 * The boolean value to return.
	 *
	 * @var bool
	 */
	protected $value;

	function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * @param bool $value
	 *
	 * @return BooleanCondition
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		return $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
	}
}
