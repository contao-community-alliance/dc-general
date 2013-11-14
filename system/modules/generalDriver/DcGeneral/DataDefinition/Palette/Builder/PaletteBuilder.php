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

namespace DcGeneral\DataDefinition\Palette\Builder;

use DcGeneral\DataDefinition\ConditionChainInterface;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Palette\Builder\Event\AddConditionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\BuilderEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreateConditionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreateDefaultPaletteConditionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreateLegendEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteCollectionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteConditionChainEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyConditionChainEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyValueConditionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\FinishConditionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\FinishLegendEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\FinishPaletteCollectionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\FinishPaletteEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\FinishPropertyEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetDefaultPaletteConditionClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetLegendClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteCollectionClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteConditionChainClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPalettePropertyValueConditionClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyConditionChainClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyValueConditionClassNameEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\UseLegendEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\UsePaletteCollectionEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\UsePaletteEvent;
use DcGeneral\DataDefinition\Palette\Builder\Event\UsePropertyEvent;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use DcGeneral\DataDefinition\Palette\LegendInterface;
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\DataDefinition\Palette\PaletteInterface;
use DcGeneral\DataDefinition\Palette\PropertyInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * The palette builder is used to build palette collections, palettes, legends, properties and conditions.
 */
class PaletteBuilder
{
	/**
	 * The condition define if the property is viewable.
	 */
	const VISIBLE = 'view';

	/**
	 * The condition define if the property is editable.
	 */
	const EDITABLE = 'edit';

	/**
	 * @var ContainerInterface
	 */
	protected $container;

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
	 * @var PaletteCollectionInterface|null
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
	 * @var PropertyConditionInterface|PaletteConditionInterface|ConditionChainInterface|null
	 */
	protected $condition = null;

	/**
	 * Factory method to create a new palette builder.
	 *
	 * @return PaletteBuilder
	 */
	static public function create(ContainerInterface $container)
	{
		return new PaletteBuilder($container);
	}

	/**
	 * Create a new palette builder.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;

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
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the palette collection class name.
	 *
	 * @param string $paletteCollectionClassName
	 */
	public function setPaletteCollectionClassName($paletteCollectionClassName)
	{
		$event = new SetPaletteCollectionClassNameEvent($paletteCollectionClassName, $this);
		$this->dispatchEvent($event);
		$paletteCollectionClassName = $event->getPaletteCollectionClassName();

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
		$event = new SetPaletteClassNameEvent($paletteClassName, $this);
		$this->dispatchEvent($event);
		$paletteClassName = $event->getPaletteClassName();

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
		$event = new SetLegendClassNameEvent($legendClassName, $this);
		$this->dispatchEvent($event);
		$legendClassName = $event->getLegendClassName();

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
		$event = new SetPropertyClassNameEvent($propertyClassName, $this);
		$this->dispatchEvent($event);
		$propertyClassName = $event->getPropertyClassName();

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
	 * @param string $paletteConditionChainClassName
	 */
	public function setPaletteConditionChainClassName($paletteConditionChainClassName)
	{
		$event = new SetPaletteConditionChainClassNameEvent($paletteConditionChainClassName, $this);
		$this->dispatchEvent($event);
		$paletteConditionChainClassName = $event->getPaletteConditionChainClassName();

		$this->paletteConditionChainClassName = (string) $paletteConditionChainClassName;
		$this->paletteConditionChainClass = new \ReflectionClass($this->paletteConditionChainClassName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaletteConditionChainClassName()
	{
		return $this->paletteConditionChainClassName;
	}

	/**
	 * Return the current palette collection object.
	 *
	 * @return PaletteCollectionInterface|null
	 */
	public function getPaletteCollection()
	{
		return $this->paletteCollection;
	}

	/**
	 * @param string $defaultPaletteConditionClassName
	 */
	public function setDefaultPaletteConditionClassName($defaultPaletteConditionClassName)
	{
		$event = new SetDefaultPaletteConditionClassNameEvent($defaultPaletteConditionClassName, $this);
		$this->dispatchEvent($event);
		$defaultPaletteConditionClassName = $event->getDefaultPaletteConditionClassName();

		$this->defaultPaletteConditionClassName = (string) $defaultPaletteConditionClassName;
		$this->defaultPaletteConditionClass = new \ReflectionClass($this->defaultPaletteConditionClassName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultPaletteConditionClassName()
	{
		return $this->defaultPaletteConditionClassName;
	}

	/**
	 * @param string $palettePropertyValueConditionClassName
	 */
	public function setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName)
	{
		$event = new SetPalettePropertyValueConditionClassNameEvent($palettePropertyValueConditionClassName, $this);
		$this->dispatchEvent($event);
		$palettePropertyValueConditionClassName = $event->getPalettePropertyValueConditionClassName();

		$this->palettePropertyValueConditionClassName = (string) $palettePropertyValueConditionClassName;
		$this->palettePropertyValueConditionClass = new \ReflectionClass($this->palettePropertyValueConditionClassName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPalettePropertyValueConditionClassName()
	{
		return $this->palettePropertyValueConditionClassName;
	}

	/**
	 * @param string $propertyConditionChainClassName
	 */
	public function setPropertyConditionChainClassName($propertyConditionChainClassName)
	{
		$event = new SetPropertyConditionChainClassNameEvent($propertyConditionChainClassName, $this);
		$this->dispatchEvent($event);
		$propertyConditionChainClassName = $event->getPalettePropertyConditionChainClassName();

		$this->propertyConditionChainClassName = (string) $propertyConditionChainClassName;
		$this->propertyConditionChainClass = new \ReflectionClass($this->propertyConditionChainClassName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPropertyConditionChainClassName()
	{
		return $this->propertyConditionChainClassName;
	}

	/**
	 * @param string $propertyValueConditionClassName
	 */
	public function setPropertyValueConditionClassName($propertyValueConditionClassName)
	{
		$event = new SetPropertyValueConditionClassNameEvent($propertyValueConditionClassName, $this);
		$this->dispatchEvent($event);
		$propertyValueConditionClassName = $event->getPropertyValueConditionClassName();

		$this->propertyValueConditionClassName = (string) $propertyValueConditionClassName;
		$this->propertyValueConditionClass = new \ReflectionClass($this->propertyValueConditionClassName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPropertyValueConditionClassName()
	{
		return $this->propertyValueConditionClassName;
	}

	/**
	 * Return the current palette object.
	 *
	 * @return PaletteInterface|null
	 */
	public function getPalette()
	{
		return $this->palette;
	}

	/**
	 * Return the current legend object.
	 *
	 * @return LegendInterface|null
	 */
	public function getLegend()
	{
		return $this->legend;
	}

	/**
	 * Return the current property object.
	 *
	 * @return PropertyInterface|null
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Return the current condition object.
	 *
	 * @return PaletteConditionInterface|PropertyConditionInterface|null
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * Reuse an existing palette collection.
	 *
	 * @param PaletteCollectionInterface $paletteCollection
	 *
	 * @return PaletteBuilder
	 */
	public function usePaletteCollection(PaletteCollectionInterface $paletteCollection)
	{
		if ($this->paletteCollection) {
			$this->finishPaletteCollection();
		}

		$event = new UsePaletteCollectionEvent($paletteCollection, $this);
		$this->dispatchEvent($event);
		$this->paletteCollection = $paletteCollection;

		return $this;
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

		$paletteCollection = $this->paletteCollectionClass->newInstance();

		$event = new CreatePaletteCollectionEvent($paletteCollection, $this);
		$this->dispatchEvent($event);
		$this->paletteCollection = $event->getPaletteCollection();

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

		$event = new FinishPaletteCollectionEvent($this->paletteCollection, $this);
		$this->dispatchEvent($event);
		$collection = $event->getPaletteCollection();

		$this->paletteCollection = null;

		return $this;
	}

	/**
	 * Reuse an existing palette.
	 *
	 * @param PaletteInterface $palette
	 *
	 * @return PaletteBuilder
	 */
	public function usePalette(PaletteInterface $palette)
	{
		if ($this->palette) {
			$this->finishPalette();
		}

		$event = new UsePaletteEvent($palette, $this);
		$this->dispatchEvent($event);
		$this->palette = $palette;

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

		$palette = $this->paletteClass->newInstance();

		if ($name) {
			$palette->setName($name);
		}

		$event = new CreatePaletteEvent($palette, $this);
		$this->dispatchEvent($event);
		$this->palette = $event->getPalette();

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

		$event = new FinishPaletteEvent($this->palette, $this);
		$this->dispatchEvent($event);
		$palette = $event->getPalette();

		if ($this->paletteCollection) {
			$this->paletteCollection->addPalette($palette);
		}

		$this->palette = null;

		return $this;
	}

	/**
	 * Reuse an existing legend.
	 *
	 * @param LegendInterface $legend
	 *
	 * @return PaletteBuilder
	 */
	public function useLegend(LegendInterface $legend)
	{
		if ($this->legend) {
			$this->finishLegend();
		}

		$event = new UseLegendEvent($legend, $this);
		$this->dispatchEvent($event);
		$this->legend = $legend;

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

		$legend = $this->legendClass->newInstance($name);

		$event = new CreateLegendEvent($legend, $this);
		$this->dispatchEvent($event);
		$this->legend = $event->getLegend();

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

		$event = new FinishLegendEvent($this->legend, $this);
		$this->dispatchEvent($event);
		$legend = $event->getLegend();

		if ($this->palette) {
			$this->palette->addLegend($legend);
		}

		$this->legend = null;

		return $this;
	}

	/**
	 * Reuse an existing property or set of properties.
	 *
	 * @param PropertyInterface[]|PropertyInterface $propertyName
	 *
	 * @return PaletteBuilder
	 */
	public function useProperty($property, $_ = null)
	{
		if ($this->property) {
			$this->finishProperty();
		}

		$properties = func_get_args();

		$this->property = array();
		foreach ($properties as $property) {
			$event = new UsePropertyEvent($property, $this);
			$this->dispatchEvent($event);

			$this->property[] = $property;
		}

		if (count($this->property) == 1) {
			$this->property = array_shift($this->property);
		}

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
			$property = $this->propertyClass->newInstance($propertyName);

			$event = new CreatePropertyEvent($property, $this);
			$this->dispatchEvent($event);
			$property = $event->getProperty();

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

		$properties = is_object($this->property) ? array($this->property) : $this->property;

		foreach ($properties as $index => $tempProperty) {
			$event = new FinishPropertyEvent($tempProperty, $this);
			$this->dispatchEvent($event);
			$properties[$index] = $event->getProperty();
		}

		if ($this->legend) {
			$this->legend->addProperties($properties);
		}

		$property = $properties;
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

			$condition = $this->paletteConditionChainClass->newInstance();
			$event = new CreatePaletteConditionChainEvent($condition, $this);
			$this->dispatchEvent($event);
			$condition = $event->getCondition();

			$event = new CreateConditionEvent($condition, $this);
			$this->dispatchEvent($event);
			$condition = $event->getCondition();

			$condition->addCondition($previousCondition);

			$this->condition = $condition;
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
		if (!$this->condition instanceof PropertyConditionChain || $this->condition->getConjunction() != $conjunction) {
			$previousCondition = $this->condition;

			$condition = $this->propertyConditionChainClass->newInstance();
			$event = new CreatePropertyConditionChainEvent($condition, $this);
			$this->dispatchEvent($event);
			$condition = $event->getPropertyConditionChain();

			$event = new CreateConditionEvent($condition, $this);
			$this->dispatchEvent($event);
			$condition = $event->getCondition();

			$condition->addCondition($previousCondition);

			$this->condition = $condition;
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

		$condition = $this->defaultPaletteConditionClass->newInstance();
		$event = new CreateDefaultPaletteConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

		$event = new CreateConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$this->condition = $event->getCondition();

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

		$condition = $this->defaultPaletteConditionClass->newInstance();
		$event = new CreateDefaultPaletteConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

		$event = new CreateConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

		$this->condition->addCondition($condition);

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
			$condition = $this->propertyValueConditionClass->newInstance();
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

		$event = new CreatePropertyValueConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getPropertyValueCondition();

		$event = new CreateConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

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
			$condition = $this->propertyValueConditionClass->newInstance();
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

		$event = new CreatePropertyValueConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getPropertyValueCondition();

		$event = new CreateConditionEvent($condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

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

		$event = new FinishConditionEvent($this->condition, $this);
		$this->dispatchEvent($event);
		$condition = $event->getCondition();

		$this->addCondition($condition);
		$this->condition = null;

		return $this;
	}

	/**
	 * Add a custom condition to last created property or palette.
	 *
	 * @param PaletteConditionInterface|PropertyConditionInterface $condition
	 */
	public function addCondition($condition, $scope = self::VISIBLE)
	{
		if ($condition instanceof PaletteConditionInterface)
		{
			if (!$this->palette) {
				throw new DcGeneralRuntimeException('Palette is missing, please create a palette first');
			}

			$event = new AddConditionEvent($condition, $this->palette, $this);
			$this->dispatchEvent($event);
			$condition = $event->getCondition();

			$previousCondition = $this->palette->getCondition();

			if (!$previousCondition) {
				$this->palette->setCondition($condition);
			}
			else if ($previousCondition instanceof PropertyConditionChain) {
				$previousCondition->addCondition($condition);
			}
			else {
				$chain = $this->paletteConditionChainClass->newInstance();
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

			$properties = is_object($this->property) ? array($this->property) : $this->property;

			foreach ($properties as $property) {
				/** @var PropertyInterface $property */
				$event = new AddConditionEvent($condition, $property, $this);
				$this->dispatchEvent($event);
				$condition = $event->getCondition();

				$previousCondition = $scope == self::EDITABLE
					? $property->getEditableCondition()
					: $property->getVisibleCondition();

				if (!$previousCondition) {
					if ($scope == self::EDITABLE) {
						$property->setEditableCondition($condition);
					}
					else {
						$property->setVisibleCondition($condition);
					}
				}
				else if ($previousCondition instanceof PropertyConditionChain) {
					$previousCondition->addCondition($condition);
				}
				else {
					$chain = $this->propertyConditionChainClass->newInstance();
					$chain->addCondition($previousCondition);
					$chain->addCondition($condition);

					if ($scope == self::EDITABLE) {
						$property->setEditableCondition($chain);
					}
					else {
						$property->setVisibleCondition($chain);
					}
				}
			}
		}

		else {
			$type = is_object($condition) ? get_class($condition) : gettype($condition);
			throw new DcGeneralInvalidArgumentException('Cannot handle condition of type [' . $type . ']');
		}

		return $this;
	}

	/**
	 * Dispatch an event over the global event dispatcher.
	 *
	 * @internal
	 * @param BuilderEvent $event
	 */
	protected function dispatchEvent(BuilderEvent $event)
	{
		global $container;
		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];
		$dispatcher->dispatch($event::NAME, $event);
	}
}
