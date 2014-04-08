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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class DefaultListingConfig.
 *
 * Default implementation of a listing config.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class DefaultListingConfig implements ListingConfigInterface
{
	/**
	 * The grouping mode to use.
	 *
	 * @var string
	 */
	protected $groupingMode;

	/**
	 * The grouing length to use.
	 *
	 * @var string
	 */
	protected $groupingLength;

	/**
	 * The default sorting mode.
	 *
	 * @var string
	 */
	protected $sortingMode;

	/**
	 * The names of the properties that will be used for sorting by default.
	 *
	 * @var array
	 */
	protected $defaultSortingFields;

	/**
	 * The properties to display in the heder (parented mode only).
	 *
	 * @var array
	 */
	protected $headerProperties;

	/**
	 * The root icon to use (hierarchical mode only).
	 *
	 * @var string
	 */
	protected $rootIcon;

	/**
	 * The root label.
	 *
	 * @var string
	 */
	protected $rootLabel;

	/**
	 * The CSS class to apply to each item in the listing.
	 *
	 * @var string
	 */
	protected $itemCssClass;

	/**
	 * The item formatter to use.
	 *
	 * @var DefaultModelFormatterConfig[]
	 */
	protected $itemFormatter;

	/**
	 * Flag if the properties displayed shall be shown as table layout.
	 *
	 * @var bool
	 */
	protected $showColumns;

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
	public function setDefaultSortingFields($value)
	{
		$this->defaultSortingFields = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultSortingFields()
	{
		return $this->defaultSortingFields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHeaderPropertyNames($value)
	{
		$this->headerProperties = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderPropertyNames()
	{
		return $this->headerProperties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootIcon($value)
	{
		$this->rootIcon = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootIcon()
	{
		return $this->rootIcon;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootLabel($value)
	{
		$this->rootLabel = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootLabel()
	{
		return $this->rootLabel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setItemCssClass($value)
	{
		$this->itemCssClass = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItemCssClass()
	{
		return $this->itemCssClass;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLabelFormatter($providerName, $value)
	{
		$this->itemFormatter[$providerName] = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasLabelFormatter($providerName)
	{
		return isset($this->itemFormatter[$providerName]);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException When no formatter has been defined.
	 */
	public function getLabelFormatter($providerName)
	{
		if (!isset($this->itemFormatter[$providerName]))
		{
			throw new DcGeneralInvalidArgumentException(
				'Formatter configuration for data provider ' . $providerName . ' is not registered.'
			);
		}

		return $this->itemFormatter[$providerName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setShowColumns($value)
	{
		$this->showColumns = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getShowColumns()
	{
		return $this->showColumns;
	}
}
