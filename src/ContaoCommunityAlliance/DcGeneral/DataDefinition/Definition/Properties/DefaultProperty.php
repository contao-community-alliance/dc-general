<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties;

/**
 * Class DefaultProperty.
 *
 * Default implementation of a property definition.
 *
 * @package DcGeneral\DataDefinition\Definition\Properties
 */
class DefaultProperty implements PropertyInterface
{

    /**
     * The property name.
     *
     * @var string
     */
    protected $name;

    /**
     * The label of the property.
     *
     * @var string
     */
    protected $label;

    /**
     * The description of the property.
     *
     * @var string
     */
    protected $description;

    /**
     * The default value of the property.
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Define if this property shall be excluded by default.
     *
     * @var bool
     */
    protected $excluded;

    /**
     * Flag if this property shall be searchable.
     *
     * @var bool
     */
    protected $searchable;

    /**
     * Flag if this property shall be sortable.
     *
     * @var bool
     */
    protected $sortable;

    /**
     * Flag if this property shall be filterable.
     *
     * @var bool
     */
    protected $filterable;

    /**
     * The grouping mode for this property.
     *
     * See ListingConfigInterface::GROUP_* flags.
     *
     * @var string
     */
    protected $groupingMode;

    /**
     * The grouing length of this property. See grouping mode.
     *
     * @var string
     */
    protected $groupingLength;

    /**
     * The sorting mode for this property.
     *
     * See ListingConfigInterface::SORT_* flags.
     *
     * @var string
     */
    protected $sortingMode;

    /**
     * The input widget type to use.
     *
     * @var string
     */
    protected $widgetType;

    /**
     * The value options for this property.
     *
     * @var array|null
     */
    protected $options;

    /**
     * The explanation string for this property.
     *
     * @var string
     */
    protected $explanation;

    /**
     * The extra information for this property.
     *
     * @var array
     */
    protected $extra;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($value)
    {
        $this->label = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function setExcluded($value)
    {
        $this->excluded = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded()
    {
        return $this->excluded;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchable($value)
    {
        $this->searchable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortable($value)
    {
        $this->sortable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterable($value)
    {
        $this->filterable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupingMode($value)
    {
        $this->groupingMode = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupingMode()
    {
        return $this->groupingMode;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupingLength($value)
    {
        $this->groupingLength = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupingLength()
    {
        return $this->groupingLength;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortingMode($value)
    {
        $this->sortingMode = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingMode()
    {
        return $this->sortingMode;
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetType($value)
    {
        $this->widgetType = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($value)
    {
        $this->options = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setExplanation($value)
    {
        $this->explanation = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtra($value)
    {
        $this->extra = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtra()
    {
        return $this->extra;
    }
}
