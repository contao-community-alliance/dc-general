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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use RuntimeException;

use function array_merge;
use function count;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Generic class to retrieve a tree collection for tree views.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TreeCollector implements EnvironmentAwareInterface
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private EnvironmentInterface $environment;

    /**
     * The panel container in use.
     *
     * @var PanelContainerInterface
     */
    private PanelContainerInterface $panel;

    /**
     * The sorting information.
     *
     * @var array
     */
    private array $sorting;

    /**
     * The tree node states that represent the current flags for the tree nodes.
     *
     * @var TreeNodeStates
     */
    private TreeNodeStates $states;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface    $environment The environment.
     * @param PanelContainerInterface $panel       The panel.
     * @param array                   $sorting     The sorting information.
     * @param TreeNodeStates          $states      The tree node states to use.
     */
    public function __construct(
        EnvironmentInterface $environment,
        PanelContainerInterface $panel,
        $sorting,
        TreeNodeStates $states
    ) {
        $this->environment = $environment;
        $this->panel       = $panel;
        $this->sorting     = $sorting;
        $this->states      = $states;
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Retrieve the panel.
     *
     * @return PanelContainerInterface
     */
    public function getPanel()
    {
        return $this->panel;
    }

    /**
     * Retrieve the sorting information.
     *
     * @return array
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Check the state of a model and set the metadata accordingly.
     *
     * @param ModelInterface $model The model of which the state shall be checked of.
     * @param int            $level The tree level the model is contained within.
     *
     * @return void
     */
    private function determineModelState(ModelInterface $model, $level)
    {
        $model->setMeta(DCGE::TREE_VIEW_LEVEL, $level);
        $model->setMeta(
            $model::SHOW_CHILDREN,
            $this->states->isModelOpen($model->getProviderName(), $model->getId())
        );
    }

    /**
     * Retrieve the child data provider names for the passed parent provider.
     *
     * @param string                                    $parentProvider The name of the parent provider.
     * @param null|ModelRelationshipDefinitionInterface $relationships  The relationship information (optional).
     *
     * @return array
     */
    private function getChildProvidersOf($parentProvider, $relationships = null)
    {
        if (null === $relationships) {
            $definition = $this->getEnvironment()->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $relationships = $definition->getModelRelationshipDefinition();
        }

        $mySubTables = [];
        foreach ($relationships->getChildConditions($parentProvider) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }

        return $mySubTables;
    }

    /**
     * Retrieve the children of a model (if any exist).
     *
     * @param DataProviderInterface         $dataProvider   The data provider.
     * @param ModelInterface                $model          The model.
     * @param ParentChildConditionInterface $childCondition The condition.
     *
     * @return CollectionInterface|null
     */
    private function getChildrenOfModel(
        DataProviderInterface $dataProvider,
        ModelInterface $model,
        ParentChildConditionInterface $childCondition
    ): ?CollectionInterface {
        $childIds = $dataProvider
            ->fetchAll(
                $dataProvider
                    ->getEmptyConfig()
                    ->setFilter($childCondition->getFilter($model))
                    ->setIdOnly(true)
            );
        assert(is_array($childIds));

        if (!count($childIds)) {
            return null;
        }

        $children = $dataProvider->fetchAll(
            $dataProvider
                ->getEmptyConfig()
                ->setSorting(['sorting' => 'ASC'])
                ->setFilter(
                    FilterBuilder::fromArray()
                        ->getFilter()
                        ->andPropertyValueIn('id', $childIds)
                        ->getAllAsArray()
                )
        );

        assert($children instanceof CollectionInterface);

        return $children;
    }

    /**
     * This "renders" a model for tree view.
     *
     * @param ModelInterface $model     The model to render.
     * @param int            $intLevel  The current level in the tree hierarchy.
     * @param array          $subTables The names of data providers that shall be rendered "below" this item.
     *
     * @return void
     */
    private function treeWalkModel(ModelInterface $model, int $intLevel, array $subTables = []): void
    {
        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $relationships = $definition->getModelRelationshipDefinition();
        $hasChildren   = false;

        $this->determineModelState($model, $intLevel);

        $providerName     = $model->getProviderName();
        $mySubTables      = $this->getChildProvidersOf($providerName, $relationships);
        $childCollections = [];
        foreach ($subTables as $subTable) {
            // Evaluate the child filter for this item.
            $childFilter = $relationships->getChildCondition($providerName, $subTable);

            // If we do not know how to render this table within here, continue with the next one.
            if (!$childFilter) {
                continue;
            }

            $dataProvider = $environment->getDataProvider($subTable);
            assert($dataProvider instanceof DataProviderInterface);

            $childCollection = $this->getChildrenOfModel($dataProvider, $model, $childFilter);
            $hasChildren     = null !== $childCollection;

            if ($hasChildren) {
                // Speed up - we may exit if we have at least one child but the parenting model is collapsed.
                if (!$model->getMeta($model::SHOW_CHILDREN)) {
                    break;
                }
                foreach ($childCollection as $childModel) {
                    // Let the child know about its parent.
                    $model->setMeta(ModelInterface::PARENT_ID, $model->getID());
                    $model->setMeta(ModelInterface::PARENT_PROVIDER_NAME, $providerName);

                    $this->treeWalkModel($childModel, ($intLevel + 1), $mySubTables);
                }
                $childCollections[] = $childCollection;
            }
        }

        // If expanded, store children.
        if ($model->getMeta($model::SHOW_CHILDREN) && count($childCollections)) {
            $model->setMeta($model::CHILD_COLLECTIONS, $childCollections);
        }

        $model->setMeta($model::HAS_CHILDREN, $hasChildren);
    }

    /**
     * Add the parent filtering to the given data config if any defined.
     *
     * @param ConfigInterface $config      The data config.
     * @param ModelInterface  $parentModel The parent model.
     *
     * @return void
     *
     * @throws RuntimeException When the parent provider does not match.
     */
    private function addParentFilter(ConfigInterface $config, ModelInterface $parentModel)
    {
        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $parentDataProvider = $basicDefinition->getParentDataProvider();
        assert(is_string($parentDataProvider));

        if ($parentDataProvider !== $parentModel->getProviderName()) {
            throw new RuntimeException(
                sprintf(
                    'Parent provider mismatch: %s vs. %s',
                    $parentDataProvider,
                    $parentModel->getProviderName()
                )
            );
        }

        $rootDataProvider = $basicDefinition->getRootDataProvider();
        assert(is_string($rootDataProvider));

        // Apply parent filtering, do this only for root elements.
        if (
            $parentCondition = $definition->getModelRelationshipDefinition()->getChildCondition(
                $parentDataProvider,
                $rootDataProvider
            )
        ) {
            $baseFilter = $config->getFilter();
            $filter     = $parentCondition->getFilter($parentModel);

            if (is_array($baseFilter)) {
                $filter = array_merge($baseFilter, $filter);
            }

            $config->setFilter($filter);
        }
    }

    /**
     * Put the base filter and sorting into a config.
     *
     * @return ConfigInterface
     */
    private function calculateRootConfig()
    {
        $registry = $this->getEnvironment()->getBaseConfigRegistry();
        assert($registry instanceof BaseConfigRegistryInterface);

        $rootConfig = $registry
            ->getBaseConfig()
            ->setSorting($this->getSorting());
        $this->getPanel()->initialize($rootConfig);

        return $rootConfig;
    }

    /**
     * Recursively retrieve a collection of all complete node hierarchy.
     *
     * @param string $rootId       The ids of the root node.
     * @param int    $level        The level the items are residing on.
     * @param string $providerName The data provider from which the root element originates from.
     *
     * @return CollectionInterface
     */
    public function getTreeCollectionRecursive($rootId, $level = 0, $providerName = null)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($providerName);
        assert($dataProvider instanceof DataProviderInterface);

        // Fetch root element.
        $rootModel = $dataProvider->fetch($this->calculateRootConfig()->setId($rootId));
        assert($rootModel instanceof ModelInterface);

        $this->treeWalkModel($rootModel, $level, $this->getChildProvidersOf($rootModel->getProviderName()));
        $rootCollection = $dataProvider->getEmptyCollection();
        $rootCollection->push($rootModel);

        return $rootCollection;
    }

    /**
     * Collect all items from real root - without root id.
     *
     * @param string         $providerName The data provider from which the root element originates from.
     * @param int            $level        The level in the tree.
     * @param ModelInterface $parentModel  The optional parent model (mode 4 parent).
     *
     * @return CollectionInterface
     */
    public function getChildrenOf($providerName, $level = 0, $parentModel = null)
    {
        $environment = $this->getEnvironment();

        $dataProvider = $environment->getDataProvider($providerName);
        assert($dataProvider instanceof DataProviderInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $rootConfig = $this->calculateRootConfig();

        $rootCondition = $definition->getModelRelationshipDefinition()->getRootCondition();
        if (null !== $rootCondition) {
            $baseFilter = $rootConfig->getFilter();
            $filter     = $rootCondition->getFilterArray();

            if (is_array($baseFilter)) {
                $filter = array_merge($baseFilter, $filter);
            }

            $rootConfig->setFilter($filter);
        }

        if ($parentModel) {
            $this->addParentFilter($rootConfig, $parentModel);
        }

        $rootCollection = $dataProvider->fetchAll($rootConfig);
        $tableTreeData  = $dataProvider->getEmptyCollection();
        assert($rootCollection instanceof CollectionInterface);

        if ($rootCollection->length() > 0) {
            $mySubTables = $this->getChildProvidersOf($providerName);

            foreach ($rootCollection as $rootModel) {
                $tableTreeData->push($rootModel);
                $this->treeWalkModel($rootModel, $level, $mySubTables);
            }
        }

        return $tableTreeData;
    }
}
