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

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

/**
 * A property contained within a palette.
 *
 * @package DcGeneral\DataDefinition\Palette
 */
class Property implements PropertyInterface
{
	/**
	 * The name of the property.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The condition to be examined to determine if this property is visible.
	 *
	 * @var PropertyConditionInterface
	 */
	protected $visibleCondition;

	/**
	 * The condition to be examined to determine if this property is editable.
	 *
	 * @var PropertyConditionInterface
	 */
	protected $editableCondition;

	/**
	 * Create a new instance.
	 *
	 * @param string $name The name of the property.
	 */
	public function __construct($name)
	{
		$this->setName($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = (string)$name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->visibleCondition)
		{
			return $this->visibleCondition->match($model, $input);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditable(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->editableCondition)
		{
			return $this->editableCondition->match($model, $input);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setVisibleCondition(PropertyConditionInterface $condition = null)
	{
		$this->visibleCondition = $condition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVisibleCondition()
	{
		return $this->visibleCondition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEditableCondition(PropertyConditionInterface $condition = null)
	{
		$this->editableCondition = $condition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditableCondition()
	{
		return $this->editableCondition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		if ($this->visibleCondition !== null)
		{
			$this->visibleCondition = clone $this->visibleCondition;
		}
		if ($this->editableCondition !== null)
		{
			$this->editableCondition = clone $this->editableCondition;
		}
	}
}
