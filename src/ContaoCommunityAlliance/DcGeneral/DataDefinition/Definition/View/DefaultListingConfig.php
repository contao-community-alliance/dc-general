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
	 * The grouping and sorting definitions.
	 *
	 * @var GroupAndSortingDefinitionCollectionInterface
	 */
	protected $groupAndSorting;

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
	 * Create a new instance.
	 */
	public function __construct()
	{
		$this->groupAndSorting = new DefaultGroupAndSortingDefinitionCollection();
	}

	/**
	 * Create a default group and sorting definition if none is present so far.
	 *
	 * @return GroupAndSortingDefinitionInterface
	 */
	protected function getOrCreateDefaultGroupAndSortingDefinition()
	{
		$definitions = $this->getGroupAndSortingDefinition();

		if (!$definitions->hasDefault())
		{
			$definitions->markDefault($definitions->add());
		}

		return $definitions->getDefault();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setGroupingMode($value)
	{
		$definition = $this->getOrCreateDefaultGroupAndSortingDefinition();

		if ($definition->getCount() == 0)
		{
			$definition->add();
		}

		$definition->get(0)->setGroupingMode($value);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGroupingMode()
	{
		$definitions = $this->getGroupAndSortingDefinition();

		if (!$definitions->hasDefault())
		{
			return GroupAndSortingInformationInterface::GROUP_NONE;
		}

		$definition = $definitions->getDefault();

		if ($definition->getCount() == 0)
		{
			return GroupAndSortingInformationInterface::GROUP_NONE;
		}

		return $definition->get(0)->getGroupingMode();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setGroupingLength($value)
	{
		$definition = $this->getOrCreateDefaultGroupAndSortingDefinition();

		if ($definition->getCount() == 0)
		{
			$definition->add();
		}

		$definition->get(0)->setGroupingLength($value);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGroupingLength()
	{
		$definitions = $this->getGroupAndSortingDefinition();

		if (!$definitions->hasDefault())
		{
			return 0;
		}

		$definition = $definitions->getDefault();

		if ($definition->getCount() == 0)
		{
			return 0;
		}

		return $definition->get(0)->getGroupingLength();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSortingMode($value)
	{
		$definition = $this->getOrCreateDefaultGroupAndSortingDefinition();

		if ($definition->getCount() == 0)
		{
			$definition->add();
		}

		$definition->get(0)->setSortingMode($value);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortingMode()
	{
		$definitions = $this->getGroupAndSortingDefinition();

		if (!$definitions->hasDefault())
		{
			return GroupAndSortingInformationInterface::SORT_RANDOM;
		}

		$definition = $definitions->getDefault();

		if ($definition->getCount() == 0)
		{
			return GroupAndSortingInformationInterface::SORT_RANDOM;
		}

		return $definition->get(0)->getSortingMode();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultSortingFields($value)
	{
		$definition = $this->getOrCreateDefaultGroupAndSortingDefinition();

		foreach ($value as $property => $direction)
		{
			$propertyInformation = $definition->add();
			$propertyInformation
				->setProperty($property)
				->setSortingMode($direction);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultSortingFields()
	{
		$definitions = $this->getGroupAndSortingDefinition();

		if (!$definitions->hasDefault())
		{
			return array();
		}

		$properties = array();
		foreach ($this->getGroupAndSortingDefinition()->getDefault() as $propertyInformation)
		{
			/** @var GroupAndSortingInformationInterface $propertyInformation */
			if ($propertyInformation->getProperty())
			{
				$properties[$propertyInformation->getProperty()] = $propertyInformation->getSortingMode();
			}
		}

		return $properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setGroupAndSortingDefinition($definition)
	{
		$this->groupAndSorting = $definition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGroupAndSortingDefinition()
	{
		return $this->groupAndSorting;
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
