<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @copyright  2013-2023 Contao Community Alliance.
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
 */
class DefaultProperty implements PropertyInterface, EmptyValueAwarePropertyInterface
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * The label of the property.
     *
     * @var string
     */
    protected $label = '';

    /**
     * The description of the property.
     *
     * @var string
     */
    protected $description = '';

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
    protected $excluded = false;

    /**
     * Flag if this property shall be searchable.
     *
     * @var bool
     */
    protected $searchable = false;

    /**
     * Flag if this property shall be sortable.
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * Flag if this property shall be filterable.
     *
     * @var bool
     */
    protected $filterable = false;

    /**
     * The input widget type to use.
     *
     * @var string
     */
    protected $widgetType = '';

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
    protected $explanation = '';

    /**
     * The extra information for this property.
     *
     * @var array
     */
    protected $extra = [];

    /**
     * Flag if an empty value has been set.
     *
     * @var bool
     */
    private $hasEmptyValue = false;

    /**
     * The empty value.
     *
     * @var mixed
     */
    private $emptyValue;

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

    /**
     * {@inheritdoc}
     */
    public function hasEmptyValue()
    {
        return $this->hasEmptyValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyValue()
    {
        return $this->emptyValue;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmptyValue($value)
    {
        $this->emptyValue    = $value;
        $this->hasEmptyValue = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetEmptyValue()
    {
        $this->emptyValue    = null;
        $this->hasEmptyValue = false;

        return $this;
    }
}
