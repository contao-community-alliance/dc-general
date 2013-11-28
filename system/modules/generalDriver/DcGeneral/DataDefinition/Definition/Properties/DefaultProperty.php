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

namespace DcGeneral\DataDefinition\Definition\Properties;

class DefaultProperty implements PropertyInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var mixed
	 */
	protected $defaultValue;

	/**
	 * @var bool
	 */
	protected $excluded;

	/**
	 * @var bool
	 */
	protected $searchable;

	/**
	 * @var bool
	 */
	protected $sortable;

	/**
	 * @var bool
	 */
	protected $filterable;

	/**
	 * @var string
	 */
	protected $groupingMode;

	/**
	 * @var string
	 */
	protected $groupingLength;

	/**
	 * @var string
	 */
	protected $sortingMode;

	/**
	 * @var string
	 */
	protected $widgetType;

	/**
	 * @var array|null
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $explanation;

	/**
	 * @var array
	 */
	protected $extra;

	/**
	 * Create an instance.
	 *
	 * @param string $name
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
