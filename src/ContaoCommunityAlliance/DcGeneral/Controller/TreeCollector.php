<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;

/**
 * Generic class to retrieve a tree collection for tree views.
 */
class TreeCollector implements EnvironmentAwareInterface
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The panel container in use.
     *
     * @var PanelContainerInterface
     */
    private $panel;

    /**
     * The sorting information.
     *
     * @var array
     */
    private $sorting;

    /**
     * The tree node states that represent the current flags for the tree nodes.
     *
     * @var TreeNodeStates
     */
    private $states;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface    $environment The environment.
     *
     * @param PanelContainerInterface $panel       The panel.
     *
     * @param array                   $sorting     The sorting information.
     *
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
        $this->sorting     = (array) $sorting;
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
     *
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
     *
     * @param null|ModelRelationshipDefinitionInterface $relationships  The relationship information (optional).
     *
     * @return array
     */
    private function getChildProvidersOf($parentProvider, $relationships = null)
    {
        if ($relationships === null) {
            $relationships = $this->getEnvironment()->getDataDefinition()->getModelRelationshipDefinition();
        }

        $mySubTables = array();
        foreach ($relationships->getChildConditions($parentProvider) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }

        return $mySubTables;
    }

    /**
     * Retrieve the children of a model (if any exist).
     *
     * @param DataProviderInterface         $dataProvider   The data provider.
     *
     * @param ModelInterface                $model          The model.
     *
     * @param ParentChildConditionInterface $childCondition The condition.
     *
     * @return CollectionInterface|null
     */
    private function getChildrenOfModel($dataProvider, $model, $childCondition)
    {
        $childIds = $dataProvider
            ->fetchAll(
                $dataProvider
                    ->getEmptyConfig()
                    ->setFilter($childCondition->getFilter($model))
                    ->setIdOnly(true)
            );

        if (!$childIds) {
            return null;
        }

        return $dataProvider->fetchAll(
            $dataProvider
                ->getEmptyConfig()
                ->setSorting(array('sorting' => 'ASC'))
                ->setFilter(
                    FilterBuilder::fromArray()
                        ->getFilter()
                        ->andPropertyValueIn('id', $childIds)
                        ->getAllAsArray()
                )
        );
    }

    /**
     * This "renders" a model for tree view.
     *
     * @param ModelInterface $model     The model to render.
     *
     * @param int            $intLevel  The current level in the tree hierarchy.
     *
     * @param array          $subTables The names of data providers that shall be rendered "below" this item.
     *
     * @return void
     */
    private function treeWalkModel(ModelInterface $model, $intLevel, $subTables = array())
    {
        $environment   = $this->getEnvironment();
        $relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();
        $hasChildren   = false;

        $this->determineModelState($model, $intLevel);

        $providerName     = $model->getProviderName();
        $mySubTables      = $this->getChildProvidersOf($providerName, $relationships);
        $childCollections = array();
        foreach ($subTables as $subTable) {
            // Evaluate the child filter for this item.
            $childFilter = $relationships->getChildCondition($providerName, $subTable);

            // If we do not know how to render this table within here, continue with the next one.
            if (!$childFilter) {
                continue;
            }

            $dataProvider    = $environment->getDataProvider($subTable);
            $childCollection = $this->getChildrenOfModel($dataProvider, $model, $childFilter);
            $hasChildren     = !!$childCollection;

            // Speed up - we may exit if we have at least one child but the parenting model is collapsed.
            if ($hasChildren && !$model->getMeta($model::SHOW_CHILDREN)) {
                break;
            } elseif ($hasChildren) {
                foreach ($childCollection as $childModel) {
                    // Let the child know about it's parent.
                    $model->setMeta(ModelInterface::PARENT_ID, $model->getID());
                    $model->setMeta(ModelInterface::PARENT_PROVIDER_NAME, $providerName);

                    $this->treeWalkModel($childModel, ($intLevel + 1), $mySubTables);
                }
                $childCollections[] = $childCollection;
            }
        }

        // If expanded, store children.
        if ($model->getMeta($model::SHOW_CHILDREN) && count($childCollections) != 0) {
            $model->setMeta($model::CHILD_COLLECTIONS, $childCollections);
        }

        $model->setMeta($model::HAS_CHILDREN, $hasChildren);
    }

    /**
     * Add the parent filtering to the given data config if any defined.
     *
     * @param ConfigInterface $config      The data config.
     *
     * @param ModelInterface  $parentModel The parent model.
     *
     * @return void
     *
     * @throws \RuntimeException When the parent provider does not match.
     */
    private function addParentFilter(ConfigInterface $config, ModelInterface $parentModel)
    {
        $environment     = $this->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $relationships   = $definition->getModelRelationshipDefinition();

        if ($basicDefinition->getParentDataProvider() !== $parentModel->getProviderName()) {
            throw new \RuntimeException(
                sprintf(
                    'Parent provider mismatch: %s vs. %s',
                    $basicDefinition->getParentDataProvider(),
                    $parentModel->getProviderName()
                )
            );
        }

        if (!$basicDefinition->getParentDataProvider()) {
            return;
        }

        // Apply parent filtering, do this only for root elements.
        if ($parentCondition = $relationships->getChildCondition(
            $basicDefinition->getParentDataProvider(),
            $basicDefinition->getRootDataProvider()
        )) {
            $baseFilter = $config->getFilter();
            $filter     = $parentCondition->getFilter($parentModel);

            if ($baseFilter) {
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
        $rootConfig = $this
            ->getEnvironment()
            ->getBaseConfigRegistry()
            ->getBaseConfig()
            ->setSorting($this->getSorting());
        $this->getPanel()->initialize($rootConfig);

        return $rootConfig;
    }

    /**
     * Recursively retrieve a collection of all complete node hierarchy.
     *
     * @param array  $rootId       The ids of the root node.
     *
     * @param int    $level        The level the items are residing on.
     *
     * @param string $providerName The data provider from which the root element originates from.
     *
     * @return CollectionInterface
     */
    public function getTreeCollectionRecursive($rootId, $level = 0, $providerName = null)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($providerName);

        // Fetch root element.
        $rootModel   = $dataProvider->fetch($this->calculateRootConfig()->setId($rootId));
        $mySubTables = $this->getChildProvidersOf($rootModel->getProviderName());

        $this->treeWalkModel($rootModel, $level, $mySubTables);
        $rootCollection = $dataProvider->getEmptyCollection();
        $rootCollection->push($rootModel);

        return $rootCollection;
    }

    /**
     * Collect all items from real root - without root id.
     *
     * @param string         $providerName The data provider from which the root element originates from.
     *
     * @param int            $level        The level in the tree.
     *
     * @param ModelInterface $parentModel  The optional parent model (mode 4 parent).
     *
     * @return CollectionInterface
     */
    public function getChildrenOf($providerName, $level = 0, $parentModel = null)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($providerName);
        $rootConfig   = $this->calculateRootConfig();

        if ($rootCondition = $environment->getDataDefinition()->getModelRelationshipDefinition()->getRootCondition()) {
            $baseFilter = $rootConfig->getFilter();
            $filter     = $rootCondition->getFilterArray();

            if ($baseFilter) {
                $filter = array_merge($baseFilter, $filter);
            }

            $rootConfig->setFilter($filter);
        }

        if ($parentModel) {
            $this->addParentFilter($rootConfig, $parentModel);
        }

        $rootCollection = $dataProvider->fetchAll($rootConfig);
        $tableTreeData  = $dataProvider->getEmptyCollection();

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
