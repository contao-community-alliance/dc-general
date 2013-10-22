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

namespace DcGeneral\DataDefinition\Palette\Condition\Palette;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\ConditionInterface;

/**
 * Condition for the default palette.
 */
class PropertyValueCondition implements PaletteConditionInterface
{
	/**
	 * The property name.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * The expected property value.
	 *
	 * @var mixed
	 */
	protected $propertyValue;

	/**
	 * Use strict compare mode.
	 *
	 * @var bool
	 */
	protected $strict;

	function __construct($propertyName = '', $propertyValue = null, $strict = false)
	{
		$this->propertyName  = (string) $propertyName;
		$this->propertyValue = $propertyValue;
		$this->strict        = (bool) $strict;
	}

	/**
	 * @param string $propertyName
	 */
	public function setPropertyName($propertyName)
	{
		$this->propertyName = (string) $propertyName;
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
	 * @param mixed $propertyValue
	 */
	public function setPropertyValue($propertyValue)
	{
		$this->propertyValue = $propertyValue;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPropertyValue()
	{
		return $this->propertyValue;
	}

	/**
	 * @param boolean $strict
	 */
	public function setStrict($strict)
	{
		$this->strict = (bool) $strict;
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
	public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if (!$this->propertyName) {
			return false;
		}

		if ($input && $input->hasPropertyValue($this->propertyName)) {
			$value = $input->getPropertyValue($this->propertyName);
		}
		else if ($model) {
			$value = $model->getProperty($this->propertyName);
		}
		else {
			return false;
		}

		return ($this->strict ? ($value === $this->propertyValue) : ($value == $this->propertyValue)) ? 1 : false;
	}
}
