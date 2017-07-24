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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class DefaultListingConfig.
 *
 * Default implementation of a listing config.
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
     * The parent table property name.
     *
     * @var string
     */
    protected $parentTablePropertyName;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->groupAndSorting = new DefaultGroupAndSortingDefinitionCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSortingFields()
    {
        $definitions = $this->getGroupAndSortingDefinition();

        if (!$definitions->hasDefault()) {
            return array();
        }

        $properties = array();
        foreach ($this->getGroupAndSortingDefinition()->getDefault() as $propertyInformation) {
            /** @var GroupAndSortingInformationInterface $propertyInformation */
            if ($propertyInformation->getProperty()) {
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
        if (!isset($this->itemFormatter[$providerName])) {
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

    /**
     * {@inheritDoc}
     */
    public function getParentTablePropertyName()
    {
        return $this->parentTablePropertyName;
    }

    /**
     * {@inheritDoc}
     */
    public function setParentTablePropertyName($propertyName)
    {
        $this->parentTablePropertyName = $propertyName;
    }
}
