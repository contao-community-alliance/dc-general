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
class PropertyFalseCondition implements PropertyConditionInterface
{
	/**
	 * The property name.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Use strict compare mode.
	 *
	 * @var bool
	 */
	protected $strict;

	function __construct($propertyName, $strict = false)
	{
		$this->propertyName  = (string) $propertyName;
		$this->strict        = (bool) $strict;
	}

	/**
	 * @param string $propertyName
	 */
	public function setPropertyName($propertyName)
	{
		$this->propertyName = $propertyName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * @param boolean $strict
	 */
	public function setStrict($strict)
	{
		$this->strict = $strict;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStrict()
	{
		return $this->strict;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($input && $input->hasPropertyValue($this->propertyName)) {
			$value = $input->getPropertyValue($this->propertyName);
		}
		else if ($model) {
			$value = $model->getProperty($this->propertyName);
		}
		else if ($this->strict) {
			return false;
		}
		else {
			return true;
		}

		return $this->strict ? ($value === false) : !$value;
	}
}
