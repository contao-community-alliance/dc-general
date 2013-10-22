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

class Property implements PropertyInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var PropertyConditionInterface
	 */
	protected $visibleCondition;

	/**
	 * @var PropertyConditionInterface
	 */
	protected $editableCondition;

	function __construct($name)
	{
		$this->setName($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
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
		if ($this->visibleCondition) {
			return $this->visibleCondition->match($model, $input);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditable(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->editableCondition) {
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
}
