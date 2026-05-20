<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties;

/**
 * Class DefaultProperty.
 *
 * Default implementation of a property definition.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @api
 */
class DefaultProperty implements PropertyInterface, EmptyValueAwarePropertyInterface
{
    /**
     * The property name.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * The label of the property.
     *
     * @var string
     */
    protected string $label = '';

    /**
     * The description of the property.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * The default value of the property.
     *
     * @var mixed
     */
    protected mixed $defaultValue = null;

    /**
     * Define if this property shall be excluded by default.
     *
     * @var bool
     */
    protected bool $excluded = false;

    /**
     * Flag if this property shall be searchable.
     *
     * @var bool
     */
    protected bool $searchable = false;

    /**
     * Flag if this property shall be sortable.
     *
     * @var bool
     */
    protected bool $sortable = false;

    /**
     * Flag if this property shall be filterable.
     *
     * @var bool
     */
    protected bool $filterable = false;

    /**
     * The input widget type to use.
     *
     * @var string
     */
    protected string $widgetType = '';

    /**
     * The value options for this property.
     *
     * @var array|null
     */
    protected ?array $options = null;

    /**
     * The explanation string for this property.
     *
     * @var string
     */
    protected string $explanation = '';

    /**
     * The extra information for this property.
     *
     * @var array
     */
    protected array $extra = [];

    /**
     * Flag if an empty value has been set.
     *
     * @var bool
     */
    private bool $hasEmptyValue = false;

    /**
     * The empty value.
     *
     * @var mixed
     */
    private mixed $emptyValue;

    /**
     * Create an instance.
     *
     * @param string $name The name of the property.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setLabel($value)
    {
        $this->label = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setExcluded($value)
    {
        $this->excluded = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isExcluded()
    {
        return $this->excluded;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setSearchable($value)
    {
        $this->searchable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setFilterable($value)
    {
        $this->filterable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setWidgetType($value)
    {
        $this->widgetType = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setOptions($value)
    {
        $this->options = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setExplanation($value)
    {
        $this->explanation = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setExtra($value)
    {
        $this->extra = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasEmptyValue()
    {
        return $this->hasEmptyValue;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEmptyValue()
    {
        return $this->emptyValue;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setEmptyValue($value)
    {
        $this->emptyValue    = $value;
        $this->hasEmptyValue = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function resetEmptyValue()
    {
        $this->emptyValue    = null;
        $this->hasEmptyValue = false;

        return $this;
    }
}
