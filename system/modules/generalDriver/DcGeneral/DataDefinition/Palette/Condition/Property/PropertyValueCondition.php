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
class PropertyValueCondition implements PropertyConditionInterface
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

	function __construct($propertyName, $propertyValue, $strict = false)
	{
		$this->propertyName  = (string) $propertyName;
		$this->propertyValue = (string) $propertyValue;
		$this->strict        = (bool) $strict;
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
		else {
			return false;
		}

		return $this->strict ? ($value === $this->propertyValue) : ($value == $this->propertyValue);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
	}
}
