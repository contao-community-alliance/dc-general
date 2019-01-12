<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

/**
 * Default implementation of the basic information about the data definition.
 */
class DefaultBasicDefinition implements BasicDefinitionInterface
{
    /**
     * The mode.
     *
     * @var int
     */
    protected $mode;

    /**
     * The name of the data provider of the root elements.
     *
     * @var string
     */
    protected $rootProviderName;

    /**
     * The name of the data provider of the parent element.
     *
     * @var string
     */
    protected $parentProviderName;

    /**
     * The name of the data provider of the elements being processed.
     *
     * @var string
     */
    protected $providerName;

    /**
     * Array of filter rules.
     *
     * @var array
     */
    protected $additionalFilter;

    /**
     * If true, only edit mode is used.
     *
     * @var bool
     */
    protected $isEditOnlyMode = false;

    /**
     * Boolean flag determining if this data container is editable.
     *
     * @var bool
     */
    protected $isEditable = true;

    /**
     * Boolean flag determining if this data container is deletable.
     *
     * @var bool
     */
    protected $isDeletable = true;

    /**
     * Determines if new entries may be created within this data container.
     *
     * @var bool
     */
    protected $isCreatable = true;

    /**
     * Determines if the view shall switch automatically into edit mode.
     *
     * @var bool
     */
    protected $switchToEditEnabled;

    /**
     * The ids of the root entries.
     *
     * @var mixed[]
     */
    protected $rootEntries = [];

    /**
     * Determines if the data container is an dynamic parent table.
     *
     * @var bool
     */
    protected $dynamicParentTable = false;

    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * {@inheritdoc}
     */
    public function setRootDataProvider($providerName)
    {
        $this->rootProviderName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDataProvider()
    {
        return $this->rootProviderName;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentDataProvider($providerName)
    {
        $this->parentProviderName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentDataProvider()
    {
        return $this->parentProviderName;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataProvider($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProvider()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalFilter($dataProvider, $filter)
    {
        $this->additionalFilter[$dataProvider] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAdditionalFilter($dataProvider = null)
    {
        if ($dataProvider === null) {
            $dataProvider = $this->getDataProvider();
        }

        return isset($this->additionalFilter[$dataProvider]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalFilter($dataProvider = null)
    {
        if ($dataProvider === null) {
            $dataProvider = $this->getDataProvider();
        }

        return $this->additionalFilter[$dataProvider];
    }

    /**
     * {@inheritdoc}
     */
    public function setEditOnlyMode($value)
    {
        $this->isEditOnlyMode = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditOnlyMode()
    {
        return $this->isEditOnlyMode;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditable($value)
    {
        $this->isEditable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable()
    {
        return $this->isEditable;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletable($value)
    {
        $this->isDeletable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeletable()
    {
        return $this->isDeletable;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatable($value)
    {
        $this->isCreatable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isCreatable()
    {
        return $this->isCreatable;
    }

    /**
     * {@inheritdoc}
     */
    public function setSwitchToEditEnabled($switchToEditEnabled)
    {
        $this->switchToEditEnabled = $switchToEditEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSwitchToEditEnabled()
    {
        return $this->switchToEditEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setRootEntries($entries)
    {
        $this->rootEntries = $entries;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootEntries()
    {
        return $this->rootEntries;
    }

    /**
     * {@inheritDoc}
     */
    public function setDynamicParentTable($dynamicParentTable)
    {
        $this->dynamicParentTable = $dynamicParentTable;

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function isDynamicParentTable()
    {
        return $this->dynamicParentTable;
    }
}
