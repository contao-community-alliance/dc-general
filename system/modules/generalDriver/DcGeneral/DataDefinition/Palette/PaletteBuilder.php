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

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * The palette builder is used to build palette collections, palettes, legends, properties and conditions.
 */
class PaletteBuilder
{
	/**
	 * @var string
	 */
	protected $paletteCollectionClassName = 'DcGeneral\DataDefinition\Palette\PaletteCollection';

	/**
	 * @var \ReflectionClass
	 */
	protected $paletteCollectionClass;

	/**
	 * @var string
	 */
	protected $paletteClassName = 'DcGeneral\DataDefinition\Palette\Palette';

	/**
	 * @var \ReflectionClass
	 */
	protected $paletteClass;

	/**
	 * @var string
	 */
	protected $legendClassName = 'DcGeneral\DataDefinition\Palette\Legend';

	/**
	 * @var \ReflectionClass
	 */
	protected $legendClass;

	/**
	 * @var string
	 */
	protected $propertyClassName = 'DcGeneral\DataDefinition\Palette\Property';

	/**
	 * @var \ReflectionClass
	 */
	protected $propertyClass;

	/**
	 * @var string
	 */
	protected $paletteConditionChainClassName = 'DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain';

	/**
	 * @var \ReflectionClass
	 */
	protected $paletteConditionChainClass;

	/**
	 * @var string
	 */
	protected $defaultPaletteConditionClassName = 'DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition';

	/**
	 * @var \ReflectionClass
	 */
	protected $defaultPaletteConditionClass;

	/**
	 * @var string
	 */
	protected $palettePropertyValueConditionClassName = 'DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition';

	/**
	 * @var \ReflectionClass
	 */
	protected $palettePropertyValueConditionClass;

	/**
	 * @var string
	 */
	protected $propertyConditionChainClassName = 'DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain';

	/**
	 * @var \ReflectionClass
	 */
	protected $propertyConditionChainClass;

	/**
	 * @var string
	 */
	protected $propertyValueConditionClassName = 'DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition';

	/**
	 * @var \ReflectionClass
	 */
	protected $propertyValueConditionClass;

	/**
	 * @var PaletteCollectionInterface
	 */
	protected $paletteCollection = null;

	/**
	 * @var PaletteInterface|null
	 */
	protected $palette = null;

	/**
	 * @var LegendInterface|null
	 */
	protected $legend = null;

	/**
	 * @var PropertyInterface|null
	 */
	protected $property = null;

	/**
	 * @var PropertyConditionInterface|PaletteConditionInterface|null
	 */
	protected $condition = null;

	/**
	 * Factory method to create a new palette builder.
	 *
	 * @return PaletteBuilder
	 */
	static public function create()
	{
		return new PaletteBuilder();
	}

	/**
	 * Create a new palette builder.
	 */
	public function __construct()
	{
		$this->paletteCollectionClass = new \ReflectionClass($this->paletteCollectionClassName);
		$this->paletteClass = new \ReflectionClass($this->paletteClassName);
		$this->legendClass = new \ReflectionClass($this->legendClassName);
		$this->propertyClass = new \ReflectionClass($this->propertyClassName);

		$this->paletteConditionChainClass = new \ReflectionClass($this->paletteConditionChainClassName);
		$this->defaultPaletteConditionClass = new \ReflectionClass($this->defaultPaletteConditionClassName);
		$this->palettePropertyValueConditionClass = new \ReflectionClass($this->palettePropertyValueConditionClassName);

		$this->propertyConditionChainClass = new \ReflectionClass($this->propertyConditionChainClassName);
		$this->propertyValueConditionClass = new \ReflectionClass($this->propertyValueConditionClassName);
	}

	/**
	 * Set the palette collection class name.
	 *
	 * @param string $paletteCollectionClassName
	 */
	public function setPaletteCollectionClassName($paletteCollectionClassName)
	{
		$this->paletteCollectionClassName = (string) $paletteCollectionClassName;
		$this->paletteCollectionClass = new \ReflectionClass($this->paletteCollectionClassName);
		return $this;
	}

	/**
	 * Return the palette collection class name.
	 *
	 * @return string
	 */
	public function getPaletteCollectionClassName()
	{
		return $this->paletteCollectionClassName;
	}

	/**
	 * Set the palette class name.
	 *
	 * @param string $paletteClassName
	 */
	public function setPaletteClassName($paletteClassName)
	{
		$this->paletteClassName = (string) $paletteClassName;
		$this->paletteClass = new \ReflectionClass($this->paletteClassName);
		return $this;
	}

	/**
	 * Return the palette class name.
	 *
	 * @return string
	 */
	public function getPaletteClassName()
	{
		return $this->paletteClassName;
	}

	/**
	 * Set the legend class name.
	 *
	 * @param string $legendClassName
	 */
	public function setLegendClassName($legendClassName)
	{
		$this->legendClassName = (string) $legendClassName;
		$this->legendClass = new \ReflectionClass($this->legendClassName);
		return $this;
	}

	/**
	 * Return the legend class name.
	 *
	 * @return string
	 */
	public function getLegendClassName()
	{
		return $this->legendClassName;
	}

	/**
	 * Set the property class name.
	 *
	 * @param string $propertyClassName
	 */
	public function setPropertyClassName($propertyClassName)
	{
		$this->propertyClassName = (string) $propertyClassName;
		$this->propertyClass = new \ReflectionClass($this->propertyClassName);
		return $this;
	}

	/**
	 * Return the property class name.
	 *
	 * @return string
	 */
	public function getPropertyClassName()
	{
		return $this->propertyClassName;
	}

	/**
	 * Start a new palette collection.
	 *
	 * @return PaletteBuilder
	 */
	public function createPaletteCollection()
	{
		if ($this->paletteCollection) {
			$this->finishPaletteCollection();
		}

		$this->paletteCollection = $this->paletteCollectionClass->newInstance();

		return $this;
	}

	/**
	 * Finish the current palette collection.
	 *
	 * @param PaletteCollectionInterface $collection Return the final palette collection.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function finishPaletteCollection(&$collection = null)
	{
		if (!$this->paletteCollection) {
			throw new DcGeneralRuntimeException('Palette collection is missing, please create a palette collection first');
		}

		if ($this->palette) {
			$this->finishPalette();
		}

		$collection = $this->paletteCollection;
		$this->paletteCollection = null;

		return $this;
	}

	/**
	 * Start a new palette.
	 *
	 * @param string|null $name (deprecated) Only for backwards compatibility, we will remove palette names in the future.
	 *
	 * @return PaletteBuilder
	 */
	public function createPalette($name = null)
	{
		if ($this->palette) {
			$this->finishPalette();
		}

		$this->palette = $this->paletteClass->newInstance();

		if ($name) {
			$this->palette->setName($name);
		}

		return $this;
	}

	/**
	 * Finish the current palette.
	 *
	 * @param PaletteInterface $palette Return the final palette.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function finishPalette(&$palette = null)
	{
		if (!$this->palette) {
			throw new DcGeneralRuntimeException('Palette is missing, please create a palette first');
		}

		if ($this->legend) {
			$this->finishLegend();
		}
		if ($this->condition) {
			$this->finishCondition();
		}
		if ($this->paletteCollection) {
			$this->paletteCollection->addPalette($this->palette);
		}

		$palette = $this->palette;
		$this->palette = null;

		return $this;
	}

	/**
	 * Start a new legend.
	 *
	 * @param string $name
	 *
	 * @return PaletteBuilder
	 */
	public function createLegend($name)
	{
		if ($this->legend) {
			$this->finishLegend();
		}

		$this->legend = $this->legendClass->newInstance();
		$this->legend->setName($name);

		return $this;
	}

	/**
	 * Finish the current legend.
	 *
	 * @param LegendInterface $legend Return the final legend.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function finishLegend(&$legend = null)
	{
		if (!$this->legend) {
			throw new DcGeneralRuntimeException('Legend is missing, please create a legend first');
		}

		if ($this->property) {
			$this->finishProperty();
		}

		if ($this->palette) {
			$this->palette->addLegend($this->legend);
		}

		$legend = $this->legend;
		$this->legend = null;

		return $this;
	}

	/**
	 * Start a new single property or set of properties.
	 *
	 * @param string $propertyName
	 *
	 * @return PaletteBuilder
	 */
	public function createProperty($propertyName, $_ = null)
	{
		if ($this->property) {
			$this->finishProperty();
		}

		$propertyNames = func_get_args();

		$this->property = array();
		foreach ($propertyNames as $propertyName) {
			$property = $this->propertyClass->newInstance();
			$property->setName($propertyName);
			$this->property[] = $property;
		}

		if (count($this->property) == 1) {
			$this->property = array_shift($this->property);
		}

		return $this;
	}

	/**
	 * Finish the current property or set of properties.
	 *
	 * @param PropertyInterface|PropertyInterface[] $property Return the final property or set of properties.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function finishProperty(&$property = null)
	{
		if (!$this->property) {
			throw new DcGeneralRuntimeException('Property is missing, please create a property first');
		}

		if ($this->condition) {
			$this->finishCondition();
		}

		if ($this->legend) {
			$this->legend->addProperties((array) $this->property);
		}

		$property = $this->property;
		$this->property = null;

		return $this;
	}

	/**
	 * Create a palette condition chain.
	 *
	 * @param $conjunction
	 */
	protected function createPaletteConditionChain()
	{
		if (!$this->condition instanceof PaletteConditionChain) {
			$previousCondition = $this->condition;
			$this->condition = $this->paletteConditionChainClass->newInstance();
			$this->condition->addCondition($previousCondition);
		}

		return $this;
	}

	/**
	 * Create a palette condition chain.
	 *
	 * @param $conjunction
	 */
	protected function createPropertyConditionChain($conjunction = PropertyConditionChain::AND_CONJUNCTION)
	{
		if (!$this->condition instanceof PropertyConditionChain && $this->condition->getConjunction() != $conjunction) {
			$previousCondition = $this->condition;
			$this->condition = $this->paletteConditionChainClass->newInstance();
			$this->condition->addCondition($previousCondition);
		}

		return $this;
	}

	/**
	 * Start a new default-palette condition.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function createDefaultPaletteCondition()
	{
		if ($this->condition) {
			$this->finishCondition();
		}

		if (!$this->palette) {
			throw new DcGeneralRuntimeException('Does not know where to create the property-value condition, please create a palette or property first');
		}
		else {
			$this->condition = $this->defaultPaletteConditionClass->newInstance();
		}

		return $this;
	}

	/**
	 * Start a new default-palette condition and chain with previous condition.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function chainDefaultPaletteCondition()
	{
		if (!$this->palette) {
			throw new DcGeneralRuntimeException('Does not know where to create the property-value condition, please create a palette or property first');
		}

		$this->createPaletteConditionChain();
		$this->condition->addCondition($this->defaultPaletteConditionClass->newInstance());

		return $this;
	}

	/**
	 * Start a new property-value condition.
	 *
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @param bool $strict
	 * @param string $conjunction
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function createPropertyValueCondition($propertyName, $propertyValue, $strict = false)
	{
		if ($this->condition) {
			$this->finishCondition();
		}

		if ($this->property) {
			$condition = $this->palettePropertyValueConditionClass->newInstance();
		}
		else if ($this->palette) {
			$condition = $this->palettePropertyValueConditionClass->newInstance();
		}
		else {
			throw new DcGeneralRuntimeException('Does not know where to create the property-value condition, please create a palette or property first');
		}

		$condition->setPropertyName($propertyName);
		$condition->setPropertyValue($propertyValue);
		$condition->setStrict($strict);

		$this->condition = $condition;

		return $this;
	}

	/**
	 * Start a new property-value condition and chain with previous condition.
	 *
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @param bool $strict
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function chainPropertyValueCondition($propertyName, $propertyValue, $strict = false, $conjunction = PropertyConditionChain::AND_CONJUNCTION)
	{
		if ($this->property) {
			$this->createPropertyConditionChain($conjunction);
			$condition = $this->palettePropertyValueConditionClass->newInstance();
		}
		else if ($this->palette) {
			$this->createPaletteConditionChain();
			$condition = $this->palettePropertyValueConditionClass->newInstance();
		}
		else {
			throw new DcGeneralRuntimeException('Does not know where to create the property-value condition, please create a palette or property first');
		}

		$condition->setPropertyName($propertyName);
		$condition->setPropertyValue($propertyValue);
		$condition->setStrict($strict);

		$this->condition->addCondition($condition);

		return $this;
	}

	/**
	 * Finish the current condition.
	 *
	 * @param PropertyConditionInterface|PaletteConditionInterface $condition Return the final condition.
	 *
	 * @return PaletteBuilder
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function finishCondition(&$condition = null)
	{
		if (!$this->condition) {
			throw new DcGeneralRuntimeException('Condition is missing, please create a condition first');
		}

		$this->addCondition($this->condition);

		$condition = $this->condition;
		$this->condition = null;

		return $this;
	}

	/**
	 * Add a custom condition to last created property or palette.
	 *
	 * @param PaletteConditionInterface|PropertyConditionInterface $condition
	 */
	public function addCondition($condition)
	{
		if ($condition instanceof PaletteConditionInterface)
		{
			if (!$this->palette) {
				throw new DcGeneralRuntimeException('Palette is missing, please create a palette first');
			}

			$previousCondition = $this->palette->getCondition();

			if (!$previousCondition) {
				$this->palette->setCondition($condition);
			}
			else if ($previousCondition instanceof PropertyConditionChain) {
				$previousCondition->addCondition($condition);
			}
			else {
				$chain = new PropertyConditionChain();
				$chain->addCondition($previousCondition);
				$chain->addCondition($condition);
				$this->palette->setCondition($chain);
			}
		}

		else if ($condition instanceof PropertyConditionInterface)
		{
			if (!$this->property) {
				throw new DcGeneralRuntimeException('Property is missing, please create a property first');
			}

			$properties = (array) $this->property;

			foreach ($properties as $property) {
				$previousCondition = $property->getCondition();

				if (!$previousCondition) {
					$property->setCondition($condition);
				}
				else if ($previousCondition instanceof PropertyConditionChain) {
					$previousCondition->addCondition($condition);
				}
				else {
					$chain = new PropertyConditionChain();
					$chain->addCondition($previousCondition);
					$chain->addCondition($condition);
					$property->setCondition($chain);
				}
			}
		}

		else {
			$type = is_object($condition) ? get_class($condition) : gettype($condition);
			throw new DcGeneralInvalidArgumentException('Cannot handle condition of type [' . $type . ']');
		}

		return $this;
	}
}
