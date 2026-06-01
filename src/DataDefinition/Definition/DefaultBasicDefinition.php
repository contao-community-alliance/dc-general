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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

/**
 * Default implementation of the basic information about the data definition.
 *
 * @api
 */
class DefaultBasicDefinition implements BasicDefinitionInterface
{
    /**
     * The mode.
     *
     * @var int|null
     */
    protected ?int $mode = null;

    /**
     * The name of the data provider of the root elements.
     *
     * @var string|null
     */
    protected ?string $rootProviderName = null;

    /**
     * The name of the data provider of the parent element.
     *
     * @var string|null
     */
    protected ?string $parentProviderName = null;

    /**
     * The name of the data provider of the elements being processed.
     *
     * @var string|null
     */
    protected ?string $providerName = null;

    /**
     * Array of filter rules.
     *
     * @var array|null
     */
    protected ?array $additionalFilter = null;

    /**
     * If true, only edit mode is used.
     *
     * @var bool
     */
    protected bool $isEditOnlyMode = false;

    /**
     * Boolean flag determining if this data container is editable.
     *
     * @var bool
     */
    protected bool $isEditable = true;

    /**
     * Boolean flag determining if this data container is deletable.
     *
     * @var bool
     */
    protected bool $isDeletable = true;

    /**
     * Determines if new entries may be created within this data container.
     *
     * @var bool
     */
    protected bool $isCreatable = true;

    /**
     * Determines if the view shall switch automatically into edit mode.
     *
     * @var bool
     */
    protected bool $switchToEditEnabled = false;

    /**
     * The ids of the root entries.
     *
     * @var mixed[]|null
     */
    protected ?array $rootEntries = [];

    /**
     * Determines if the data container is an dynamic parent table.
     *
     * @var bool
     */
    protected bool $dynamicParentTable = false;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setRootDataProvider($providerName)
    {
        $this->rootProviderName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getRootDataProvider()
    {
        return $this->rootProviderName;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setParentDataProvider($providerName)
    {
        $this->parentProviderName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParentDataProvider()
    {
        return $this->parentProviderName;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDataProvider($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDataProvider()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setAdditionalFilter($dataProvider, $filter)
    {
        $this->additionalFilter[$dataProvider] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasAdditionalFilter($dataProvider = null)
    {
        if (null === $dataProvider) {
            $dataProvider = $this->getDataProvider();
        }

        if (null === $dataProvider) {
            return false;
        }

        /** @psalm-suppress MixedReturnStatement */
        return $this->additionalFilter[$dataProvider] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getAdditionalFilter($dataProvider = null)
    {
        if (null === $dataProvider) {
            $dataProvider = $this->getDataProvider();
        }

        if (null === $dataProvider) {
            return [];
        }

        /** @psalm-suppress MixedReturnStatement */
        return  $this->additionalFilter[$dataProvider] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setEditOnlyMode($value)
    {
        $this->isEditOnlyMode = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isEditOnlyMode()
    {
        return $this->isEditOnlyMode;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setEditable($value)
    {
        $this->isEditable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isEditable()
    {
        return $this->isEditable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDeletable($value)
    {
        $this->isDeletable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isDeletable()
    {
        return $this->isDeletable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setCreatable($value)
    {
        $this->isCreatable = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isCreatable()
    {
        return $this->isCreatable;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setSwitchToEditEnabled($switchToEditEnabled)
    {
        $this->switchToEditEnabled = $switchToEditEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isSwitchToEditEnabled()
    {
        return $this->switchToEditEnabled;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setRootEntries($entries)
    {
        $this->rootEntries = $entries;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getRootEntries()
    {
        return $this->rootEntries;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setDynamicParentTable($dynamicParentTable)
    {
        $this->dynamicParentTable = $dynamicParentTable;

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function isDynamicParentTable()
    {
        return $this->dynamicParentTable;
    }
}
