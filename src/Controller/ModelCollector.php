<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class provides methods for retrieval of models.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModelCollector
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The mode the definition is in.
     *
     * @var int|null
     */
    private $definitionMode;

    /**
     * The relationship information list.
     *
     * @var ModelRelationshipDefinitionInterface
     */
    private $relationships;

    /**
     * The root condition.
     *
     * @var RootConditionInterface|null
     */
    private $rootCondition;

    /**
     * The root data provider.
     *
     * @var DataProviderInterface|null
     */
    private $rootProvider;

    /**
     * The root data provider name.
     *
     * @var string|null
     */
    private $rootProviderName;

    /**
     * The parent data provider.
     *
     * @var DataProviderInterface|null
     */
    private $parentProvider;

    /**
     * The parent data provider name.
     *
     * @var string|null
     */
    private $parentProviderName;

    /**
     * The default data provider name.
     *
     * @var string|null
     */
    private $defaultProviderName;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @throws DcGeneralRuntimeException When no root condition is specified and running in hierarchical mode.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $this->definitionMode      = $basicDefinition->getMode();
        $this->relationships       = $definition->getModelRelationshipDefinition();
        $this->defaultProviderName = $basicDefinition->getDataProvider();

        if (BasicDefinitionInterface::MODE_HIERARCHICAL === $this->definitionMode) {
            $this->rootCondition    = $this->relationships->getRootCondition();
            $this->rootProviderName = $basicDefinition->getRootDataProvider();
            $this->rootProvider     = $this->environment->getDataProvider($this->rootProviderName);

            if (!$this->rootCondition instanceof RootConditionInterface) {
                throw new DcGeneralRuntimeException('No root condition specified for hierarchical mode.');
            }

            if ($this->environment->getParentDataDefinition()) {
                $this->parentProviderName = $basicDefinition->getParentDataProvider();
                $this->parentProvider     = $this->environment->getDataProvider($this->parentProviderName);
            }
        }
        if (BasicDefinitionInterface::MODE_PARENTEDLIST === $this->definitionMode) {
            $this->parentProviderName = $basicDefinition->getParentDataProvider();
            $this->parentProvider     = $this->environment->getDataProvider($this->parentProviderName);
        }
    }

    /**
     * Fetch a certain model from its provider.
     *
     * @param ModelIdInterface|string $modelId      This is either the id of the model or a serialized id.
     * @param string|null             $providerName The name of the provider, if this is empty, the id will be
     *                                              deserialized and the provider name will get extracted from there.
     *
     * @return ModelInterface|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @throws \InvalidArgumentException When the model id is invalid.
     */
    public function getModel($modelId, $providerName = null)
    {
        if (\is_string($modelId)) {
            if (null !== $providerName) {
                $modelId = ModelId::fromValues($providerName, $modelId);
            } else {
                $modelId = ModelId::fromSerialized($modelId);
            }
        }

        if (!($modelId instanceof ModelIdInterface)) {
            throw new \InvalidArgumentException('Invalid model id passed: ' . \var_export($modelId, true));
        }

        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $parentDefinition = $this->environment->getParentDataDefinition();

        $dataProvider = $this->environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $config = $dataProvider->getEmptyConfig();
        $config->setId($modelId->getId());

        if ($definition->getName() === $modelId->getDataProviderName()) {
            $propertyDefinition = $definition->getPropertiesDefinition();
        } elseif ($parentDefinition && $parentDefinition->getName() === $modelId->getDataProviderName()) {
            $propertyDefinition = $parentDefinition->getPropertiesDefinition();
        } else {
            $propertyDefinition = null;
        }
        if (null !== $propertyDefinition) {
            $properties = [];
            // Filter real properties from the property definition.
            foreach ($propertyDefinition->getPropertyNames() as $propertyName) {
                if ($dataProvider->fieldExists($propertyName)) {
                    $properties[] = $propertyName;
                    continue;
                }

                // @codingStandardsIgnoreStart
                @\trigger_error(
                    'Only real property is allowed in the property definition.' .
                    'This will no longer be supported in the future.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
            }
            $config->setFields($properties);
        }

        return $dataProvider->fetch($config);
    }

    /**
     * Search the parent of the passed model in the passed collection.
     *
     * This recursively tries to load the parent from sub collections in sub providers.
     *
     * @param ModelInterface      $model  The model to search the parent for.
     * @param CollectionInterface $models The collection to search in.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException When the model does not originate from the child provider.
     */
    public function searchParentOfIn(ModelInterface $model, CollectionInterface $models)
    {
        $this->guardModelOriginatesFromProvider($model);

        foreach ($models as $candidate) {
            foreach ($this->relationships->getChildConditions($candidate->getProviderName()) as $condition) {
                if ($condition->matches($candidate, $model)) {
                    return $candidate;
                }

                $provider = $this->environment->getDataProvider($condition->getDestinationName());
                assert($provider instanceof DataProviderInterface);

                $config = $provider->getEmptyConfig()->setFilter($condition->getFilter($candidate));
                $parentCollection = $provider->fetchAll($config);
                assert($parentCollection instanceof CollectionInterface);
                $result = $this->searchParentOfIn($model, $parentCollection);
                if (null !== $result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Search the parent model for the given model.
     *
     * If the model is part of a hierarchical structure the parent node is determined instead of a possible available
     * parent relationship.
     *
     * @param ModelInterface $model The model for which the parent shall be retrieved.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException When a root model has been passed or not in hierarchical mode.
     * @throws DcGeneralInvalidArgumentException When the model does not originate from the child provider.
     */
    public function searchParentOf(ModelInterface $model)
    {
        switch ($this->definitionMode) {
            case BasicDefinitionInterface::MODE_HIERARCHICAL:
                return $this->searchParentOfInHierarchical($model);
            case BasicDefinitionInterface::MODE_PARENTEDLIST:
                return $this->searchParentOfInParentedMode($model);
            default:
        }

        throw new DcGeneralInvalidArgumentException('Invalid condition, not in hierarchical mode!');
    }

    /**
     * Search the parent model from a hierarchical model.
     *
     * @param ModelInterface $model The hierarchical model for search the parent model.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException It throws a exception if the configuration not passed.
     */
    public function searchParentFromHierarchical(ModelInterface $model): ?ModelInterface
    {
        if (null === $this->rootProvider) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. The root data provider must be defined!'
            );
        }
        if (null === $this->parentProvider) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. The parent data provider must be defined!'
            );
        }
        if ($this->rootProviderName !== $model->getProviderName()) {
            throw new DcGeneralInvalidArgumentException(
                'Model originates from ' . $model->getProviderName() .
                ' but is expected to be from ' . ($this->rootProviderName ?? '') .
                ' can not determine parent.'
            );
        }

        $parentProviderName = $this->parentProviderName;
        assert(\is_string($parentProviderName));

        $rootProviderName = $this->rootProviderName;
        assert(\is_string($rootProviderName));

        $condition = $this->relationships->getChildCondition($parentProviderName, $rootProviderName);
        if (null === $condition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }
        if (null !== ($inverseFilter = $condition->getInverseFilterFor($model))) {
            return $this->parentProvider->fetch($this->parentProvider->getEmptyConfig()->setFilter($inverseFilter));
        }

        $parentCollection = $this->parentProvider->fetchAll($this->parentProvider->getEmptyConfig());
        assert($parentCollection instanceof CollectionInterface);
        foreach ($parentCollection as $candidate) {
            if ($condition->matches($candidate, $model)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Retrieve all siblings of a given model.
     *
     * @param ModelInterface        $model           The model for which the siblings shall be retrieved from.
     * @param string|null           $sortingProperty The property name to use for sorting.
     * @param ModelIdInterface|null $parentId        The (optional) parent id to use.
     *
     * @return CollectionInterface
     *
     */
    public function collectSiblingsOf(
        ModelInterface $model,
        $sortingProperty = null,
        ModelIdInterface $parentId = null
    ) {
        $registry = $this->environment->getBaseConfigRegistry();
        assert($registry instanceof BaseConfigRegistryInterface);

        $config = $registry->getBaseConfig($parentId);
        // Add the parent filter.
        $this->addParentFilter($model, $config);

        if (null !== $sortingProperty) {
            $config->setSorting([$sortingProperty => 'ASC']);
        }

        // Handle grouping.
        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        if ($viewDefinition instanceof Contao2BackendViewDefinitionInterface) {
            $listingConfig        = $viewDefinition->getListingConfig();
            /** @psalm-suppress DeprecatedMethod */
            $sortingProperties    = \array_keys($listingConfig->getDefaultSortingFields());
            $sortingPropertyIndex = \array_search($sortingProperty, $sortingProperties);

            if (false !== $sortingPropertyIndex && $sortingPropertyIndex > 0) {
                $sortingProperties = \array_slice($sortingProperties, 0, $sortingPropertyIndex);

                $filters = $config->getFilter();
                assert(\is_array($filters));

                foreach ($sortingProperties as $propertyName) {
                    $filters[] = [
                        'operation' => '=',
                        'property'  => $propertyName,
                        'value'     => $model->getProperty($propertyName)
                    ];
                }

                $config->setFilter($filters);
            }
        }

        $dataProvider = $this->environment->getDataProvider($model->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $siblingCollection = $dataProvider->fetchAll($config);
        assert($siblingCollection instanceof CollectionInterface);
        return $siblingCollection;
    }

    /**
     * Scan for children of a given model.
     *
     * This method returns all models with child recursion.
     *
     * @param ModelInterface $model        The model to assemble children from.
     * @param string         $providerName The name of the data provider to fetch children from.
     *
     * @return array
     */
    public function collectChildrenOf(ModelInterface $model, $providerName = '')
    {
        return $this->internalCollectChildrenOf($model, $providerName, true);
    }

    /**
     * Scan for children of a given model.
     *
     * This method returns all models without child recursion.
     *
     * @param ModelInterface $model        The model to assemble children from.
     * @param string         $providerName The name of the data provider to fetch children from.
     *
     * @return array
     */
    public function collectDirectChildrenOf(ModelInterface $model, $providerName = '')
    {
        return $this->internalCollectChildrenOf($model, $providerName);
    }

    /**
     * Scan for children of a given model.
     *
     * This method is ready for mixed hierarchy and will return all children and grandchildren for the given table
     * (or originating table of the model, if no provider name has been given) for all levels and parent child
     * conditions.
     *
     * @param ModelInterface $model        The model to assemble children from.
     * @param string         $providerName The name of the data provider to fetch children from.
     * @param bool           $recursive    Determine for recursive sampling. For models with included child models.
     *
     * @return list<string>
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function internalCollectChildrenOf(
        ModelInterface $model,
        string $providerName = '',
        bool $recursive = false
    ) {
        if ('' === $providerName) {
            $providerName = $model->getProviderName();
        }

        $ids = ($model->getProviderName() === $providerName) ? [$model->getId()] : [];

        // Check all data providers for children of the given element.
        $childIds = [];
        foreach ($this->relationships->getChildConditions($model->getProviderName()) as $condition) {
            $provider = $this->environment->getDataProvider($condition->getDestinationName());
            assert($provider instanceof DataProviderInterface);

            $config = $provider->getEmptyConfig();
            $config->setFilter($condition->getFilter($model));

            $result = $provider->fetchAll($config);
            assert($result instanceof CollectionInterface);
            if (!$recursive && $result->length() === 0) {
                return [];
            }
            foreach ($result as $child) {
                /** @var ModelInterface $child */

                if (!$recursive && $child->getProviderName() === $providerName) {
                    $ids[] = $child->getId();
                }

                if (false === $recursive) {
                    continue;
                }
                // Head into recursion.
                $childIds[] = $this->collectChildrenOf($child, $providerName);
            }
        }

        return \array_values(\array_merge($ids, ...$childIds));
    }

    /**
     * Search the parent of a model in parented mode.
     *
     * @param ModelInterface $model The model to search the parent of.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException When the model does not originate from the child provider.
     */
    private function searchParentOfInParentedMode(ModelInterface $model)
    {
        $this->guardParentProviderDefined();

        $defaultProviderName = $this->defaultProviderName;
        assert(\is_string($defaultProviderName));

        $parentProviderName = $this->parentProviderName;
        assert(\is_string($parentProviderName));

        $condition = $this->relationships->getChildCondition($parentProviderName, $defaultProviderName);

        if (null === $condition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }

        $parentProvider = $this->parentProvider;
        assert($parentProvider instanceof DataProviderInterface);

        if (null !== ($inverseFilter = $condition->getInverseFilterFor($model))) {
            return $parentProvider->fetch($parentProvider->getEmptyConfig()->setFilter($inverseFilter));
        }
        $parentCollection = $parentProvider->fetchAll($parentProvider->getEmptyConfig());
        assert($parentCollection instanceof CollectionInterface);
        foreach ($parentCollection as $candidate) {
            if ($condition->matches($candidate, $model)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Search the parent of a model in hierarchical mode.
     *
     * @param ModelInterface $model The model to search the parent of.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException When a root model has been passed.
     */
    private function searchParentOfInHierarchical(ModelInterface $model)
    {
        $this->guardRootProviderDefined();

        foreach ($this->relationships->getChildConditions() as $condition) {
            // Skip conditions where the destination is not the provider
            if (
                $this->defaultProviderName !== $condition->getDestinationName()
                || $this->defaultProviderName !== $condition->getSourceName()
            ) {
                continue;
            }

            if (null === ($inverseFilter = $condition->getInverseFilterFor($model))) {
                continue;
            }

            $provider = $this->environment->getDataProvider($condition->getSourceName());
            assert($provider instanceof DataProviderInterface);

            $config   = $provider->getEmptyConfig()->setFilter($inverseFilter);
            $parent   = $provider->fetch($config);

            if (null !== $parent) {
                return $parent;
            }
        }
        // Start from the root data provider and walk through the whole tree.
        // To speed up, some conditions have an inverse filter - we should use them!
        $rootProvider = $this->rootProvider;
        assert($rootProvider instanceof DataProviderInterface);
        $rootCondition = $this->rootCondition;
        assert($rootCondition instanceof RootConditionInterface);
        $config = $rootProvider->getEmptyConfig()->setFilter($rootCondition->getFilterArray());
        $parentCollection = $rootProvider->fetchAll($config);
        assert($parentCollection instanceof CollectionInterface);
        return $this->searchParentOfIn($model, $parentCollection);
    }

    /**
     * Add the parent filter matching the parent of a model.
     *
     * @param ModelInterface  $model  The model to search the parent for.
     * @param ConfigInterface $config The configuration to add the parent filter to.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Parent could not be found, are the parent child conditions correct.
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     */
    private function addParentFilter(ModelInterface $model, $config)
    {
        // Not hierarchical, nothing to do.
        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $this->definitionMode) {
            return;
        }

        // Root model?
        if ($this->isRootModel($model)) {
            $rootCondition = $this->rootCondition;
            assert($rootCondition instanceof RootConditionInterface);

            $config->setFilter($rootCondition->getFilterArray());
            return;
        }

        // Determine the hard way now.
        $parent = $this->searchParentOf($model);

        if (!$parent instanceof ModelInterface) {
            throw new DcGeneralRuntimeException(
                'Parent could not be found, are the parent child conditions correct?'
            );
        }

        $condition = $this->relationships->getChildCondition($parent->getProviderName(), $model->getProviderName());
        if (null === $condition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }
        $config->setFilter($condition->getFilter($parent));
    }


    /**
     * Check if the passed model is a root model.
     *
     * @param ModelInterface $model The model to check.
     *
     * @return bool
     */
    private function isRootModel(ModelInterface $model)
    {
        return (null !== $this->rootCondition) && $this->rootCondition->matches($model);
    }

    /**
     * Guards that a root provider is defined.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When not root provider is defined.
     */
    private function guardRootProviderDefined(): void
    {
        if (null === $this->rootProvider) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. The root data provider must be defined!'
            );
        }
    }

    /**
     * Guards that a parent provider is defined.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When not root provider is defined.
     */
    private function guardParentProviderDefined(): void
    {
        if (null === $this->parentProvider) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. The parent data provider must be defined!'
            );
        }
    }

    /**
     * This guard checks if the model belongs to the configured data provider.
     *
     * @param ModelInterface $model The model to check.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When model is not for the configured provider.
     */
    private function guardModelOriginatesFromProvider(ModelInterface $model): void
    {
        if ($this->defaultProviderName === $model->getProviderName()) {
            return;
        }

        throw new DcGeneralInvalidArgumentException(
            'Model originates from ' . $model->getProviderName() .
            ' but is expected to be from ' . ($this->defaultProviderName ?? '') .
            ' can not determine parent.'
        );
    }
}
