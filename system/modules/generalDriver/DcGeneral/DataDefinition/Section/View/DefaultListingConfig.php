<?php

namespace DcGeneral\DataDefinition\Section\View;

class DefaultListingConfig implements ListingConfigInterface
{
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
	 * @var array
	 */
	protected $defaultSortingFields;

	/**
	 * @var array
	 */
	protected $headerProperties;

	/**
	 * @var string
	 */
	protected $rootIcon;

	/**
	 * @var string
	 */
	protected $itemCssClass;

	/**
	 * @var \DcGeneral\View\ModelFormatterInterface
	 */
	protected $itemFormatter;

	/**
	 * Set the grouping mode.
	 *
	 * @param string $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setGroupingMode($value)
	{
		$this->groupingMode = $value;

		return $this;
	}

	/**
	 * Return the grouping mode.
	 *
	 * @return string
	 */
	public function getGroupingMode()
	{
		return $this->groupingMode;
	}

	/**
	 * Set the grouping length.
	 *
	 * @param int $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setGroupingLength($value)
	{
		$this->groupingLength = $value;

		return $this;
	}

	/**
	 * The grouping length is used for char or digit grouping and define
	 * how many chars or digits should be respected when group.
	 *
	 * @return int
	 */
	public function getGroupingLength()
	{
		return $this->groupingLength;
	}

	/**
	 * Set the list sorting mode.
	 *
	 * @param string $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setSortingMode($value)
	{
		$this->sortingMode = $value;

		return $this;
	}

	/**
	 * Return the list sorting mode.
	 * This sorting is applied after grouping and could also be called "in-group sorting".
	 *
	 * @return string
	 */
	public function getSortingMode()
	{
		return $this->sortingMode;
	}

	/**
	 * Set the default sorting fields.
	 *
	 * @param array $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setDefaultSortingFields($value)
	{
		$this->defaultSortingFields = $value;

		return $this;
	}

	/**
	 * Get the default sorting fields which are used if the user does not define a sorting.
	 *
	 * @return array
	 */
	public function getDefaultSortingFields()
	{
		return $this->defaultSortingFields;
	}

	/**
	 * Set the list of parent's model property names.
	 *
	 * @param array $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setHeaderPropertyNames($value)
	{
		$this->headerProperties = $value;

		return $this;
	}

	/**
	 * Return a list of parent's model property names, which are shown above the item list.
	 *
	 * @return array
	 */
	public function getHeaderPropertyNames()
	{
		return $this->headerProperties;
	}

	/**
	 * Set the icon path to the root item's icon.
	 *
	 * @param  $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setRootIcon($value)
	{
		$this->rootIcon = $value;

		return $this;
	}

	/**
	 * Return the icon path to the root item's icon.
	 *
	 * @return string
	 */
	public function getRootIcon()
	{
		return $this->rootIcon;
	}

	/**
	 * Set the css classes that should be added to the items container.
	 *
	 * @param string $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setItemCssClass($value)
	{
		$this->itemCssClass = $value;

		return $this;
	}

	/**
	 * Return css classes that should be added to the items container.
	 *
	 * @return string
	 */
	public function getItemCssClass()
	{
		return $this->itemCssClass;
	}

	/**
	 * Set the label formatter.
	 *
	 * @param \DcGeneral\View\ModelFormatterInterface $value
	 *
	 * @return ListingConfigInterface
	 */
	public function setLabelFormatter($value)
	{
		$this->itemFormatter = $value;

		return $this;
	}

	/**
	 * Return the label formatter.
	 *
	 * @return \DcGeneral\View\ModelFormatterInterface
	 */
	public function getLabelFormatter()
	{
		return $this->itemFormatter;
	}
}
