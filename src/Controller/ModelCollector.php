<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class provides methods for retrieval of models.
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
     * @var int
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
     * @var DataProviderInterface
     */
    private $rootProvider;

    /**
     * The root data provider name.
     *
     * @var string
     */
    private $rootProviderName;

    /**
     * The parent data provider.
     *
     * @var DataProviderInterface
     */
    private $parentProvider;

    /**
     * The parent data provider name.
     *
     * @var string
     */
    private $parentProviderName;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @throws DcGeneralRuntimeException When no root condition is specified and running in hierarchical mode.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment         = $environment;
        $definition                = $this->environment->getDataDefinition();
        $basicDefinition           = $definition->getBasicDefinition();
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
        }
        if (BasicDefinitionInterface::MODE_PARENTEDLIST === $this->definitionMode) {
            $this->parentProviderName = $basicDefinition->getParentDataProvider();
            $this->parentProvider     = $this->environment->getDataProvider($this->parentProviderName);
        }
    }

    /**
     * Fetch a certain model from its provider.
     *
     * @param string|ModelIdInterface $modelId      This is either the id of the model or a serialized id.
     * @param string|null             $providerName The name of the provider, if this is empty, the id will be
     *                                              deserialized and the provider name will get extracted from there.
     *
     * @return ModelInterface
     *
     * @throws \InvalidArgumentException When the model id is invalid.
     */
    public function getModel($modelId, $providerName = null)
    {
        if (\is_string($modelId)) {
            try {
                $modelId = ModelId::fromValues($providerName, $modelId);
            } catch (\Exception $swallow) {
                $modelId = ModelId::fromSerialized($modelId);
            }
        }

        if (!($modelId instanceof ModelIdInterface)) {
            throw new \InvalidArgumentException('Invalid model id passed: ' . \var_export($modelId, true));
        }

        $definition       = $this->environment->getDataDefinition();
        $parentDefinition = $this->environment->getParentDataDefinition();
        $dataProvider     = $this->environment->getDataProvider($modelId->getDataProviderName());
        $config           = $dataProvider->getEmptyConfig();

        if ($definition->getName() === $modelId->getDataProviderName()) {
            $propertyDefinition = $definition->getPropertiesDefinition();
        } elseif ($parentDefinition->getName() === $modelId->getDataProviderName()) {
            $propertyDefinition = $parentDefinition->getPropertiesDefinition();
        } else {
            throw new \InvalidArgumentException('Invalid provider name ' . $modelId->getDataProviderName());
        }

        $properties = [];
        // Filter real properties from the property definition.
        foreach ($propertyDefinition->getPropertyNames() as $propertyName) {
            if ($dataProvider->fieldExists($propertyName)) {
                $properties[] = 'sourceTable.' . $propertyName;

                continue;
            }

            // @codingStandardsIgnoreStart
            @\trigger_error
            (
                'Only real property is allowed in the property definition.' .
                'This will no longer be supported in the future.',
                E_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        $config->setId($modelId->getId())->setFields($properties);

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
     * @return ModelInterface
     */
    public function searchParentOfIn(ModelInterface $model, CollectionInterface $models)
    {
        foreach ($models as $candidate) {
            /** @var ModelInterface $candidate */
            foreach ($this->relationships->getChildConditions($candidate->getProviderName()) as $condition) {
                if ($condition->matches($candidate, $model)) {
                    return $candidate;
                }

                $provider = $this->environment->getDataProvider($condition->getDestinationName());
                $config   = $provider->getEmptyConfig()->setFilter($condition->getFilter($candidate));
                $result   = $this->searchParentOfIn($model, $provider->fetchAll($config));
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
     * @param ModelInterface $model The model for which the parent shall be retrieved.
     *
     * @return ModelInterface|null
     *
     * @throws DcGeneralInvalidArgumentException When a root model has been passed or not in hierarchical mode.
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
     * Retrieve all siblings of a given model.
     *
     * @param ModelInterface   $model           The model for which the siblings shall be retrieved from.
     * @param string|null      $sortingProperty The property name to use for sorting.
     * @param ModelIdInterface $parentId        The (optional) parent id to use.
     *
     * @return CollectionInterface
     *
     * @throws DcGeneralRuntimeException When no parent model can be located.
     */
    public function collectSiblingsOf(
        ModelInterface $model,
        $sortingProperty = null,
        ModelIdInterface $parentId = null
    ) {
        $provider = $this->environment->getDataProvider($model->getProviderName());
        $config   = $this->environment->getBaseConfigRegistry()->getBaseConfig($parentId);
        // Add the parent filter.
        $this->addParentFilter($model, $config);

        if (null !== $sortingProperty) {
            $config->setSorting([(string) $sortingProperty => 'ASC']);
        }

        // Handle grouping.
        $definition = $this->environment->getDataDefinition();
        /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        if ($viewDefinition && $viewDefinition instanceof Contao2BackendViewDefinitionInterface) {
            $listingConfig        = $viewDefinition->getListingConfig();
            $sortingProperties    = \array_keys((array) $listingConfig->getDefaultSortingFields());
            $sortingPropertyIndex = \array_search($sortingProperty, $sortingProperties);

            if (false !== $sortingPropertyIndex && $sortingPropertyIndex > 0) {
                $sortingProperties = \array_slice($sortingProperties, 0, $sortingPropertyIndex);
                $filters           = $config->getFilter();

                foreach ($sortingProperties as $propertyName) {
                    $filters[] = [
                        'operation' => '=',
                        'property'  => $propertyName,
                        'value'     => $model->getProperty($propertyName),
                    ];
                }

                $config->setFilter($filters);
            }
        }

        return $provider->fetchAll($config);
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
        return $this->internalCollectChildrenOf($model, $providerName, false);
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
     * @return array
     */
    private function internalCollectChildrenOf(ModelInterface $model, $providerName = '', $recursive = false)
    {
        if ('' === $providerName) {
            $providerName = $model->getProviderName();
        }

        $ids = [];

        if ($model->getProviderName() === $providerName) {
            $ids = [$model->getId()];
        }

        // Check all data providers for children of the given element.
        $conditions = $this->relationships->getChildConditions($model->getProviderName());
        foreach ($conditions as $condition) {
            $provider = $this->environment->getDataProvider($condition->getDestinationName());
            $config   = $provider->getEmptyConfig();
            $config->setFilter($condition->getFilter($model));

            foreach ($provider->fetchAll($config) as $child) {
                /** @var ModelInterface $child */
                if ($child->getProviderName() === $providerName) {
                    $ids[] = $child->getId();
                }

                if ($recursive === false) {
                    continue;
                }
                // Head into recursion.
                $ids = \array_merge($ids, $this->collectChildrenOf($child, $providerName));
            }
        }

        return $ids;
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
        if ($model->getProviderName() !== $this->defaultProviderName) {
            throw new DcGeneralInvalidArgumentException(
                'Model originates from ' . $model->getProviderName() .
                ' but is expected to be from ' . $this->defaultProviderName .
                ' can not determine parent.'
            );
        }

        $condition = $this->relationships->getChildCondition($this->parentProviderName, $this->defaultProviderName);
        $config    = $this->parentProvider->getEmptyConfig();
        // This is pretty expensive, we fetch all models from the parent provider here.
        // This can be much faster by using the inverse condition if present.
        foreach ($this->parentProvider->fetchAll($config) as $candidate) {
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
        if ($this->isRootModel($model)) {
            throw new DcGeneralInvalidArgumentException('Invalid condition, root models can not have parents!');
        }
        // Start from the root data provider and walk through the whole tree.
        // To speed up, some conditions have an inverse filter - we should use them!
        $config = $this->rootProvider->getEmptyConfig()->setFilter($this->rootCondition->getFilterArray());

        return $this->searchParentOfIn($model, $this->rootProvider->fetchAll($config));
    }

    /**
     * Add the parent filter matching the parent of a model.
     *
     * @param ModelInterface  $model  The model to search the parent for.
     * @param ConfigInterface $config The configuration to add the parent filter to.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When parent could not be found.
     */
    private function addParentFilter(ModelInterface $model, $config)
    {
        // Not hierarchical, nothing to do.
        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $this->definitionMode) {
            return;
        }

        // Root model?
        if ($this->isRootModel($model)) {
            $config->setFilter($this->rootCondition->getFilterArray());
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
}
