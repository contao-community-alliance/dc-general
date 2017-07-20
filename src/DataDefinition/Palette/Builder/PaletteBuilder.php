<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\AddConditionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\BuilderEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreateConditionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreateDefaultPaletteConditionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreateLegendEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteCollectionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteConditionChainEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePaletteEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyConditionChainEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyValueConditionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\FinishConditionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\FinishLegendEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\FinishPaletteCollectionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\FinishPaletteEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\FinishPropertyEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetDefaultPaletteConditionClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetLegendClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteCollectionClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteConditionChainClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\
SetPalettePropertyValueConditionClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyConditionChainClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyValueConditionClassNameEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\UseLegendEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\UsePaletteCollectionEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\UsePaletteEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event\UsePropertyEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * The palette builder is used to build palette collections, palettes, legends, properties and conditions.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
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
     * The data definition container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The class name of the class to use for palette collections.
     *
     * @var string
     */
    protected $paletteCollectionClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollection';

    /**
     * The class to use for palette collections.
     *
     * @var \ReflectionClass
     */
    protected $paletteCollectionClass;

    /**
     * The class name of the class to use for palettes.
     *
     * @var string
     */
    protected $paletteClassName = 'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette';

    /**
     * The class to use for palettes.
     *
     * @var \ReflectionClass
     */
    protected $paletteClass;

    /**
     * The class name of the class to use for palette legends.
     *
     * @var string
     */
    protected $legendClassName = 'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend';

    /**
     * The class to use for palette legends.
     *
     * @var \ReflectionClass
     */
    protected $legendClass;

    /**
     * The class name of the class to use for palette properties.
     *
     * @var string
     */
    protected $propertyClassName = 'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property';

    /**
     * The class to use for palette properties.
     *
     * @var \ReflectionClass
     */
    protected $propertyClass;

    /**
     * The class name of the class to use for palette condition chains.
     *
     * @var string
     */
    protected $paletteConditionChainClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain';

    /**
     * The the class to use for palette condition chains.
     *
     * @var \ReflectionClass
     */
    protected $paletteConditionChainClass;

    /**
     * The class name of the class to use for palette conditions.
     *
     * @var string
     */
    protected $defaultPaletteConditionClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition';

    /**
     * The the class to use for palette conditions.
     *
     * @var \ReflectionClass
     */
    protected $defaultPaletteConditionClass;

    /**
     * The class name of the class to use for property value conditions.
     *
     * @var string
     */
    protected $palettePropertyValueConditionClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition';

    /**
     * The class to use for property value conditions.
     *
     * @var \ReflectionClass
     */
    protected $palettePropertyValueConditionClass;

    /**
     * The class name of the class to use for property condition chains.
     *
     * @var string
     */
    protected $propertyConditionChainClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain';

    /**
     * The class to use for property condition chains.
     *
     * @var \ReflectionClass
     */
    protected $propertyConditionChainClass;

    /**
     * The class name of the class to use for property value conditions.
     *
     * @var string
     */
    protected $propertyValueConditionClassName =
        'ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition';

    /**
     * The the class to use for property value conditions.
     *
     * @var \ReflectionClass
     */
    protected $propertyValueConditionClass;

    /**
     * The palette collection currently working on.
     *
     * @var PaletteCollectionInterface|null
     */
    protected $paletteCollection = null;

    /**
     * The palette currently working on.
     *
     * @var PaletteInterface|null
     */
    protected $palette = null;

    /**
     * The legend currently working on.
     *
     * @var LegendInterface|null
     */
    protected $legend = null;

    /**
     * The property currently working on.
     *
     * @var PropertyInterface|null
     */
    protected $property = null;

    /**
     * The condition currently working on.
     *
     * @var PropertyConditionInterface|PaletteConditionInterface|ConditionChainInterface|null
     */
    protected $condition = null;

    /**
     * Factory method to create a new palette builder.
     *
     * @param ContainerInterface $container The data definition container for which the palettes shall get built.
     *
     * @return PaletteBuilder
     */
    public static function create(ContainerInterface $container)
    {
        return new PaletteBuilder($container);
    }

    /**
     * Create a new palette builder.
     *
     * @param ContainerInterface $container The container for which the palettes shall be built.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->paletteCollectionClass = new \ReflectionClass($this->paletteCollectionClassName);
        $this->paletteClass           = new \ReflectionClass($this->paletteClassName);
        $this->legendClass            = new \ReflectionClass($this->legendClassName);
        $this->propertyClass          = new \ReflectionClass($this->propertyClassName);

        $this->paletteConditionChainClass         = new \ReflectionClass($this->paletteConditionChainClassName);
        $this->defaultPaletteConditionClass       = new \ReflectionClass($this->defaultPaletteConditionClassName);
        $this->palettePropertyValueConditionClass = new \ReflectionClass($this->palettePropertyValueConditionClassName);

        $this->propertyConditionChainClass = new \ReflectionClass($this->propertyConditionChainClassName);
        $this->propertyValueConditionClass = new \ReflectionClass($this->propertyValueConditionClassName);
    }

    /**
     * Retrieve the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the palette collection class name.
     *
     * @param string $paletteCollectionClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPaletteCollectionClassName($paletteCollectionClassName)
    {
        $event = new SetPaletteCollectionClassNameEvent($paletteCollectionClassName, $this);
        $this->dispatchEvent($event);
        $paletteCollectionClassName = $event->getPaletteCollectionClassName();

        $this->paletteCollectionClassName = (string) $paletteCollectionClassName;
        $this->paletteCollectionClass     = new \ReflectionClass($this->paletteCollectionClassName);
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
     * @param string $paletteClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPaletteClassName($paletteClassName)
    {
        $event = new SetPaletteClassNameEvent($paletteClassName, $this);
        $this->dispatchEvent($event);
        $paletteClassName = $event->getPaletteClassName();

        $this->paletteClassName = (string) $paletteClassName;
        $this->paletteClass     = new \ReflectionClass($this->paletteClassName);
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
     * @param string $legendClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setLegendClassName($legendClassName)
    {
        $event = new SetLegendClassNameEvent($legendClassName, $this);
        $this->dispatchEvent($event);
        $legendClassName = $event->getLegendClassName();

        $this->legendClassName = (string) $legendClassName;
        $this->legendClass     = new \ReflectionClass($this->legendClassName);
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
     * @param string $propertyClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPropertyClassName($propertyClassName)
    {
        $event = new SetPropertyClassNameEvent($propertyClassName, $this);
        $this->dispatchEvent($event);
        $propertyClassName = $event->getPropertyClassName();

        $this->propertyClassName = (string) $propertyClassName;
        $this->propertyClass     = new \ReflectionClass($this->propertyClassName);
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
     * Set the palette condition chain class name.
     *
     * @param string $paletteConditionChainClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPaletteConditionChainClassName($paletteConditionChainClassName)
    {
        $event = new SetPaletteConditionChainClassNameEvent($paletteConditionChainClassName, $this);
        $this->dispatchEvent($event);
        $paletteConditionChainClassName = $event->getPaletteConditionChainClassName();

        $this->paletteConditionChainClassName = (string) $paletteConditionChainClassName;
        $this->paletteConditionChainClass     = new \ReflectionClass($this->paletteConditionChainClassName);
        return $this;
    }

    /**
     * Return the palette condition chain class name.
     *
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
     * Set the default palette condition class name.
     *
     * @param string $defaultPaletteConditionClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setDefaultPaletteConditionClassName($defaultPaletteConditionClassName)
    {
        $event = new SetDefaultPaletteConditionClassNameEvent($defaultPaletteConditionClassName, $this);
        $this->dispatchEvent($event);
        $defaultPaletteConditionClassName = $event->getDefaultPaletteConditionClassName();

        $this->defaultPaletteConditionClassName = (string) $defaultPaletteConditionClassName;
        $this->defaultPaletteConditionClass     = new \ReflectionClass($this->defaultPaletteConditionClassName);
        return $this;
    }

    /**
     * Return the default palette condition class name.
     *
     * @return string
     */
    public function getDefaultPaletteConditionClassName()
    {
        return $this->defaultPaletteConditionClassName;
    }

    /**
     * Set the palette property value condition class name.
     *
     * @param string $palettePropertyValueConditionClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName)
    {
        $event = new SetPalettePropertyValueConditionClassNameEvent($palettePropertyValueConditionClassName, $this);
        $this->dispatchEvent($event);
        $palettePropertyValueConditionClassName = $event->getPalettePropertyValueConditionClassName();

        $this->palettePropertyValueConditionClassName = (string) $palettePropertyValueConditionClassName;
        $this->palettePropertyValueConditionClass     = new \ReflectionClass(
            $this->palettePropertyValueConditionClassName
        );
        return $this;
    }

    /**
     * Return the palette property value condition class name.
     *
     * @return string
     */
    public function getPalettePropertyValueConditionClassName()
    {
        return $this->palettePropertyValueConditionClassName;
    }

    /**
     * Set the property condition chain class name.
     *
     * @param string $propertyConditionChainClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPropertyConditionChainClassName($propertyConditionChainClassName)
    {
        $event = new SetPropertyConditionChainClassNameEvent($propertyConditionChainClassName, $this);
        $this->dispatchEvent($event);
        $propertyConditionChainClassName = $event->getPalettePropertyConditionChainClassName();

        $this->propertyConditionChainClassName = (string) $propertyConditionChainClassName;
        $this->propertyConditionChainClass     = new \ReflectionClass($this->propertyConditionChainClassName);
        return $this;
    }

    /**
     * Return the property condition chain class name.
     *
     * @return string
     */
    public function getPropertyConditionChainClassName()
    {
        return $this->propertyConditionChainClassName;
    }

    /**
     * Set the property value condition class name.
     *
     * @param string $propertyValueConditionClassName The class name.
     *
     * @return PaletteBuilder
     */
    public function setPropertyValueConditionClassName($propertyValueConditionClassName)
    {
        $event = new SetPropertyValueConditionClassNameEvent($propertyValueConditionClassName, $this);
        $this->dispatchEvent($event);
        $propertyValueConditionClassName = $event->getPropertyValueConditionClassName();

        $this->propertyValueConditionClassName = (string) $propertyValueConditionClassName;
        $this->propertyValueConditionClass     = new \ReflectionClass($this->propertyValueConditionClassName);
        return $this;
    }

    /**
     * Return the property value condition class name.
     *
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
     * @param PaletteCollectionInterface $paletteCollection The palette collection to reuse.
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
     * @throws DcGeneralRuntimeException When no collection is stored.
     */
    public function finishPaletteCollection(&$collection = null)
    {
        if (!$this->paletteCollection) {
            throw new DcGeneralRuntimeException(
                'Palette collection is missing, please create a palette collection first'
            );
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
     * @param PaletteInterface $palette The palette to reuse.
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
     * @param string|null $name Only for backwards compatibility, We will remove palette names in the future
     *                          (deprecated).
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
     * @throws DcGeneralRuntimeException When no palette is stored in the builder.
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
     * @param LegendInterface $legend The legend.
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
     * @param string $name Name of the legend.
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
     * @throws DcGeneralRuntimeException When no legend is stored in the builder.
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
     * @param PropertyInterface $property The first property.
     *
     * @param PropertyInterface $_        Any more subsequent properties to be used.
     *
     * @return PaletteBuilder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.CamelCaseParameterName)
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
     * @param string            $propertyName The name of the property.
     *
     * @param PropertyInterface $_            Any more subsequent property names to be used.
     *
     * @return PaletteBuilder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.CamelCaseParameterName)
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
     * @throws DcGeneralRuntimeException When no property is stored in the builder.
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
     * @return PaletteBuilder
     */
    protected function createPaletteConditionChain()
    {
        if (!$this->condition instanceof PaletteConditionChain) {
            $previousCondition = $this->condition;

            $condition = $this->paletteConditionChainClass->newInstance();
            $event     = new CreatePaletteConditionChainEvent($condition, $this);
            $this->dispatchEvent($event);
            $condition = $event->getPaletteConditionChain();

            $event = new CreateConditionEvent($condition, $this);
            $this->dispatchEvent($event);
            $condition = $event->getCondition();
            /** @var ConditionChainInterface $condition */
            $condition->addCondition($previousCondition);

            $this->condition = $condition;
        }

        return $this;
    }

    /**
     * Create a palette condition chain.
     *
     * @param string $conjunction The conjunction to use (defaults to AND).
     *
     * @return PaletteBuilder
     */
    protected function createPropertyConditionChain($conjunction = PropertyConditionChain::AND_CONJUNCTION)
    {
        if (!$this->condition instanceof PropertyConditionChain || $this->condition->getConjunction() != $conjunction) {
            $previousCondition = $this->condition;

            $condition = $this->propertyConditionChainClass->newInstance();
            $event     = new CreatePropertyConditionChainEvent($condition, $this);
            $this->dispatchEvent($event);
            $condition = $event->getPropertyConditionChain();

            $event = new CreateConditionEvent($condition, $this);
            $this->dispatchEvent($event);
            $condition = $event->getCondition();
            /** @var ConditionChainInterface $condition */
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
     * @throws DcGeneralRuntimeException When no palette or property has been stored.
     */
    public function createDefaultPaletteCondition()
    {
        if ($this->condition) {
            $this->finishCondition();
        }

        if (!$this->palette) {
            throw new DcGeneralRuntimeException(
                'Does not know where to create the property-value condition, please create a palette or property first'
            );
        }

        $condition = $this->defaultPaletteConditionClass->newInstance();
        $event     = new CreateDefaultPaletteConditionEvent($condition, $this);
        $this->dispatchEvent($event);
        $condition = $event->getDefaultPaletteCondition();

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
     * @throws DcGeneralRuntimeException When no palette or property has been stored.
     */
    public function chainDefaultPaletteCondition()
    {
        if (!$this->palette) {
            throw new DcGeneralRuntimeException(
                'Does not know where to create the property-value condition, please create a palette or property first'
            );
        }

        $this->createPaletteConditionChain();

        $condition = $this->defaultPaletteConditionClass->newInstance();
        $event     = new CreateDefaultPaletteConditionEvent($condition, $this);
        $this->dispatchEvent($event);
        $condition = $event->getDefaultPaletteCondition();

        $event = new CreateConditionEvent($condition, $this);
        $this->dispatchEvent($event);
        $condition = $event->getCondition();

        $this->condition->addCondition($condition);

        return $this;
    }

    /**
     * Start a new property-value condition.
     *
     * @param string $propertyName  The name of the property.
     *
     * @param mixed  $propertyValue The value of the property.
     *
     * @param bool   $strict        Flag if the comparison shall be strict (type safe).
     *
     * @return PaletteBuilder
     *
     * @throws DcGeneralRuntimeException If neither a palette nor a property is stored in the builder.
     */
    public function createPropertyValueCondition($propertyName, $propertyValue, $strict = false)
    {
        if ($this->condition) {
            $this->finishCondition();
        }

        if ($this->property) {
            $condition = $this->propertyValueConditionClass->newInstance();
        } elseif ($this->palette) {
            $condition = $this->palettePropertyValueConditionClass->newInstance();
        } else {
            throw new DcGeneralRuntimeException(
                'Does not know where to create the property-value condition, please create a palette or property first'
            );
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
     * @param string $propertyName  The name of the property.
     *
     * @param mixed  $propertyValue The value of the property.
     *
     * @param bool   $strict        Flag if the comparison shall be strict (type safe).
     *
     * @param string $conjunction   The conjunction.
     *
     * @return PaletteBuilder
     *
     * @throws DcGeneralRuntimeException If neither a palette nor a property is stored in the builder.
     */
    public function chainPropertyValueCondition(
        $propertyName,
        $propertyValue,
        $strict = false,
        $conjunction = PropertyConditionChain::AND_CONJUNCTION
    ) {
        if ($this->property) {
            $this->createPropertyConditionChain($conjunction);
            $condition = $this->propertyValueConditionClass->newInstance();
        } elseif ($this->palette) {
            $this->createPaletteConditionChain();
            $condition = $this->palettePropertyValueConditionClass->newInstance();
        } else {
            throw new DcGeneralRuntimeException(
                'Does not know where to create the property-value condition, please create a palette or property first'
            );
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
     * @throws DcGeneralRuntimeException If no condition is stored in the builder.
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
     * Add a custom condition to last created palette.
     *
     * @param PaletteConditionInterface $condition The condition to add.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If the palette is missing.
     */
    protected function addPaletteCondition(PaletteConditionInterface $condition)
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
        } elseif ($previousCondition instanceof PropertyConditionChain) {
            $previousCondition->addCondition($condition);
        } else {
            $chain = $this->paletteConditionChainClass->newInstance();
            $chain->addCondition($previousCondition);
            $chain->addCondition($condition);
            $this->palette->setCondition($chain);
        }
    }

    /**
     * Add a custom condition to last created property.
     *
     * @param PropertyConditionInterface $condition The condition to add.
     *
     * @param string                     $scope     The scope.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If the the property is missing.
     */
    protected function addPropertyCondition(PropertyConditionInterface $condition, $scope = self::VISIBLE)
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
                } else {
                    $property->setVisibleCondition($condition);
                }
            } elseif ($previousCondition instanceof PropertyConditionChain) {
                $previousCondition->addCondition($condition);
            } else {
                $chain = $this->propertyConditionChainClass->newInstance();
                $chain->addCondition($previousCondition);
                $chain->addCondition($condition);

                if ($scope == self::EDITABLE) {
                    $property->setEditableCondition($chain);
                } else {
                    $property->setVisibleCondition($chain);
                }
            }
        }
    }

    /**
     * Add a custom condition to last created property or palette.
     *
     * @param PaletteConditionInterface|PropertyConditionInterface $condition The condition to add.
     *
     * @param string                                               $scope     The scope.
     *
     * @return PaletteBuilder
     *
     * @throws DcGeneralInvalidArgumentException When an unknown condition type is passed.
     */
    public function addCondition($condition, $scope = self::VISIBLE)
    {
        if ($condition instanceof PaletteConditionInterface) {
            $this->addPaletteCondition($condition);
        } elseif ($condition instanceof PropertyConditionInterface) {
            $this->addPropertyCondition($condition, $scope);
        } else {
            $type = is_object($condition) ? get_class($condition) : gettype($condition);
            throw new DcGeneralInvalidArgumentException('Cannot handle condition of type [' . $type . ']');
        }

        return $this;
    }

    /**
     * Dispatch an event over the global event dispatcher.
     *
     * @param BuilderEvent $event The event to dispatch.
     *
     * @return void
     *
     * @internal
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function dispatchEvent(BuilderEvent $event)
    {
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        $dispatcher = $GLOBALS['container']['event-dispatcher'];
        $dispatcher->dispatch($event::NAME, $event);
    }
}
