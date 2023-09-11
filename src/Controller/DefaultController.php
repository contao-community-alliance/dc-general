<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Stefan Lindecke <github.com@chektrion.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelManipulator;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class serves as main controller class in dc general.
 *
 * It holds various methods for data manipulation and retrieval that is non view related.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @psalm-suppress MissingConstructor
 */
class DefaultController implements ControllerInterface
{
    /**
     * The attached environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The relationship manager.
     *
     * @var RelationshipManager
     */
    private $relationshipManager;

    /**
     * The model collector.
     *
     * @var ModelCollector
     */
    private $modelCollector;

    /**
     * Error message.
     *
     * @var string
     */
    protected $notImplMsg =
        '<div style="text-align:center; font-weight:bold; padding:40px;">
        The function/view &quot;%s&quot; is not implemented.<br />Please
        <a
            target="_blank"
            style="text-decoration:underline"
            href="https://github.com/contao-community-alliance/dc-general/issues">support us</a>
        to add this important feature!</div>';

    /**
     * Throw an exception that an unknown method has been called.
     *
     * @param string $name      Method name.
     * @param array  $arguments The method arguments.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Always.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __call($name, $arguments)
    {
        throw new DcGeneralRuntimeException('Error Processing Request: ' . $name, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $mode = $definition->getBasicDefinition()->getMode();
        assert(\is_int($mode));

        $this->relationshipManager = new RelationshipManager(
            $definition->getModelRelationshipDefinition(),
            $mode
        );
        $this->modelCollector      = new ModelCollector($this->environment);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Action $action)
    {
        $event = new ActionEvent($this->getEnvironment(), $action);

        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, DcGeneralEvents::ACTION);

        return (string) $event->getResponse();
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOfIn().
     *
     * @see ModelCollector::searchParentOfIn
     */
    public function searchParentOfIn(ModelInterface $model, CollectionInterface $models)
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOfIn().',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $parent = $this->modelCollector->searchParentOfIn($model, $models);
        if (null === $parent) {
            throw new \RuntimeException('Not found');
        }

        return $parent;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a root model has been passed or not in hierarchical mode.
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOf().
     *
     * @see ModelCollector::searchParentOf
     */
    public function searchParentOf(ModelInterface $model)
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOf().',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $parent = $this->modelCollector->searchParentOf($model);
        if (null === $parent) {
            throw new \RuntimeException('Not found');
        }

        return $parent;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectChildrenOf().
     *
     * @see ModelCollector::collectChildrenOf
     */
    public function assembleAllChildrenFrom($model, $providerName = '')
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectChildrenOf()',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return $this->modelCollector->collectChildrenOf($model, $providerName);
    }

    /**
     * Retrieve all siblings of a given model.
     *
     * @param ModelInterface        $model           The model for which the siblings shall be retrieved from.
     * @param null                  $sortingProperty The property name to use for sorting.
     * @param ModelIdInterface|null $parentId        The (optional) parent id to use.
     *
     * @return CollectionInterface
     *
     * @deprecated Use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectSiblingsOf().
     *
     * @see        ModelCollector::collectSiblingsOf
     */
    protected function assembleSiblingsFor(
        ModelInterface $model,
        $sortingProperty = null,
        ModelIdInterface $parentId = null
    ) {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectSiblingsOf()',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return $this->modelCollector->collectSiblingsOf($model, $sortingProperty, $parentId);
    }

    /**
     * Retrieve children of a given model.
     *
     * @param ModelInterface $model           The model for which the children shall be retrieved.
     * @param string|null    $sortingProperty The property name to use for sorting.
     *
     * @return CollectionInterface
     *
     * @throws DcGeneralRuntimeException Unable to retrieve children in non-hierarchical mode.
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     */
    protected function assembleChildrenFor(ModelInterface $model, $sortingProperty = null)
    {
        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $provider = $environment->getDataProvider($model->getProviderName());
        assert($provider instanceof DataProviderInterface);

        $registry = $environment->getBaseConfigRegistry();
        assert($registry instanceof BaseConfigRegistryInterface);

        $config        = $registry->getBaseConfig();
        $relationships = $definition->getModelRelationshipDefinition();

        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $definition->getBasicDefinition()->getMode()) {
            throw new DcGeneralRuntimeException('Unable to retrieve children in non hierarchical mode.');
        }

        $condition = $relationships->getChildCondition($model->getProviderName(), $model->getProviderName());
        if (null === $condition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }
        $config->setFilter($condition->getFilter($model));

        if ($sortingProperty) {
            $config->setSorting([$sortingProperty => 'ASC']);
        }

        $childrenCollection = $provider->fetchAll($config);
        assert($childrenCollection instanceof CollectionInterface);
        return $childrenCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function updateModelFromPropertyBag($model, $propertyValues)
    {
        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $properties = $definition->getPropertiesDefinition();

        ModelManipulator::updateModelFromPropertyBag($properties, $model, $propertyValues);

        return $this;
    }

    /**
     * Return all supported languages from the default data data provider.
     *
     * @param mixed $mixID The id of the item for which to retrieve the valid languages.
     *
     * @return array
     */
    public function getSupportedLanguages($mixID)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();

        // Check if current data provider supports multi language.
        if ($dataProvider instanceof MultiLanguageDataProviderInterface) {
            $supportedLanguages = $dataProvider->getLanguages($mixID);
        } else {
            $supportedLanguages = null;
        }

        // Check if we have some languages.
        if (null === $supportedLanguages) {
            return [];
        }

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        // Make an array from the collection.
        $languages = [];
        foreach ($supportedLanguages as $value) {
            /** @var LanguageInformationInterface $value */
            $locale = $value->getLocale();

            $languages[$locale] = $translator->translate('LNG.' . $locale, 'languages');
        }

        return $languages;
    }

    /**
     * Handle a property in a cloned model.
     *
     * @param ModelInterface        $model        The cloned model.
     * @param PropertyInterface     $property     The property to handle.
     * @param DataProviderInterface $dataProvider The data provider the model originates from.
     *
     * @return void
     */
    private function handleClonedModelProperty(
        ModelInterface $model,
        PropertyInterface $property,
        DataProviderInterface $dataProvider
    ) {
        $extra    = $property->getExtra();
        $propName = $property->getName();

        // Check doNotCopy.
        if (isset($extra['doNotCopy']) && (true === $extra['doNotCopy'])) {
            $model->setProperty($propName, null);
            return;
        }

        // Check uniqueness.
        if (
            isset($extra['unique'])
            && (true === $extra['unique'])
            && !$dataProvider->isUniqueValue($propName, $model->getProperty($propName))
        ) {
            // Implicit "do not copy" unique values, they cannot be unique anymore.
            $model->setProperty($propName, null);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException For constraint violations.
     */
    public function createClonedModel($model)
    {
        $clone = clone $model;
        $clone->setId(null);

        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $properties   = $definition->getPropertiesDefinition();
        $dataProvider = $environment->getDataProvider($clone->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        foreach (\array_keys($clone->getPropertiesAsArray()) as $propName) {
            // If the property is not known, remove it.
            if (!$properties->hasProperty($propName)) {
                continue;
            }

            $property = $properties->getProperty($propName);
            $this->handleClonedModelProperty($clone, $property, $dataProvider);
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException When the model id is invalid.
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel().
     *
     * @see ModelCollector::getModel
     */
    public function fetchModelFromProvider($modelId, $providerName = null)
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel()',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $model = $this->modelCollector->getModel($modelId, $providerName);
        if (null === $model) {
            throw new \RuntimeException('Not found');
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function createEmptyModelWithDefaults()
    {
        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $propertyDefinition = $definition->getPropertiesDefinition();
        $properties         = $propertyDefinition->getProperties();
        $model              = $dataProvider->getEmptyModel();

        foreach ($properties as $property) {
            $propName = $property->getName();

            if (null !== $property->getDefaultValue()) {
                $model->setProperty($propName, $property->getDefaultValue());
            }
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getModelFromClipboardItem(ItemInterface $item)
    {
        $modelId = $item->getModelId();

        if (!$modelId) {
            return null;
        }

        return $this->modelCollector->getModel($modelId);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelsFromClipboardItems(array $items)
    {
        $environment = $this->getEnvironment();
        $models      = new DefaultCollection();

        foreach ($items as $item) {
            /** @var ItemInterface $item */
            if (null !== ($modelId = $item->getModelId())) {
                // Make sure model exists.
                if (null !== ($model = $this->modelCollector->getModel($modelId))) {
                    $models->push($model);
                }
                continue;
            }

            $dataProvider = $environment->getDataProvider($item->getDataProviderName());
            assert($dataProvider instanceof DataProviderInterface);

            $models->push($dataProvider->getEmptyModel());
        }

        return $models;
    }

    /**
     * {@inheritDoc}
     */
    public function getModelsFromClipboard(ModelIdInterface $parentModelId = null)
    {
        $environment = $this->getEnvironment();

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $basicDefinition   = $dataDefinition->getBasicDefinition();
        $modelProviderName = $basicDefinition->getDataProvider();
        assert(\is_string($modelProviderName));

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $filter = new Filter();
        $filter->andModelIsFromProvider($modelProviderName);
        if ($parentModelId) {
            $filter->andParentIsFromProvider($parentModelId->getDataProviderName());
        } else {
            $filter->andHasNoParent();
        }

        return $this->getModelsFromClipboardItems($clipboard->fetch($filter));
    }

    /**
     * {@inheritDoc}
     */
    public function applyClipboardActions(
        ModelIdInterface $source = null,
        ModelIdInterface $after = null,
        ModelIdInterface $into = null,
        ModelIdInterface $parentModelId = null,
        FilterInterface $filter = null,
        array &$items = []
    ) {
        if ($source) {
            $actions = $this->getActionsFromSource($source, $parentModelId);
        } else {
            $actions = $this->fetchModelsFromClipboard($filter, $parentModelId);
        }

        return $this->doActions($actions, $after, $into, $parentModelId, $items);
    }

    /**
     * Fetch actions from source.
     *
     * @param ModelIdInterface      $source        The source id.
     * @param ModelIdInterface|null $parentModelId The parent id.
     *
     * @return array
     *
     * @throws \InvalidArgumentException When the model id is invalid.
     */
    private function getActionsFromSource(ModelIdInterface $source, ModelIdInterface $parentModelId = null)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $dataProvider = $basicDefinition->getDataProvider();
        assert(\is_string($dataProvider));

        $filter = new Filter();
        $filter->andModelIsFromProvider($dataProvider);
        if ($basicDefinition->getParentDataProvider()) {
            $parentDataProvider = $basicDefinition->getDataProvider();
            assert(\is_string($parentDataProvider));

            $filter->andParentIsFromProvider($parentDataProvider);
        } else {
            $filter->andHasNoParent();
        }
        $filter->andModelIs($source);

        $clipboard = $this->getEnvironment()->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $item = $clipboard->fetch($filter)[0] ?? null;

        $action = $item ? $item->getAction() : ItemInterface::CUT;
        $model  = $this->modelCollector->getModel($source);
        assert($model instanceof ModelInterface);

        return [
            [
                'model' => $model,
                'item'  => new Item($action, $parentModelId, ModelId::fromModel($model))
            ]
        ];
    }

    /**
     * Fetch actions from the clipboard.
     *
     * @param FilterInterface|null  $filter        The clipboard filter.
     * @param ModelIdInterface|null $parentModelId The parent id.
     *
     * @return array
     */
    private function fetchModelsFromClipboard(FilterInterface $filter = null, ModelIdInterface $parentModelId = null)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (!$filter) {
            $filter = new Filter();
        }

        if ($filter instanceof Filter) {
            $basicDefinition   = $dataDefinition->getBasicDefinition();
            $modelProviderName = $basicDefinition->getDataProvider();
            assert(\is_string($modelProviderName));
            $filter->andModelIsFromProvider($modelProviderName);
            if ($parentModelId) {
                $filter->andParentIsFromProvider($parentModelId->getDataProviderName());
            } else {
                $filter->andHasNoParent();
            }
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $items   = $clipboard->fetch($filter);
        $actions = [];

        foreach ($items as $item) {
            $model = null;

            if (!$item->isCreate() && null !== ($model = $item->getModelId())) {
                $model = $this->modelCollector->getModel((string) $model->getId(), $item->getDataProviderName());
            }

            $actions[] = [
                'model' => $model,
                'item'  => $item,
            ];
        }

        return $actions;
    }

    /**
     * Effectively do the actions.
     *
     * @param array                 $actions       The action's collection.
     * @param ModelIdInterface|null $after         The previous model id.
     * @param ModelIdInterface|null $into          The hierarchical parent model id.
     * @param ModelIdInterface|null $parentModelId The parent model id.
     * @param array                 $items         Write-back clipboard items.
     *
     * @return CollectionInterface
     */
    private function doActions(
        array $actions,
        ModelIdInterface $after = null,
        ModelIdInterface $into = null,
        ModelIdInterface $parentModelId = null,
        array &$items = []
    ) {
        if ($parentModelId) {
            $parentModel = $this->modelCollector->getModel($parentModelId);
        } else {
            $parentModel = null;
        }

        // Holds models, that need deep-copy.
        $deepCopyList = [];

        // Apply to create and copy actions.
        foreach ($actions as &$action) {
            $this->applyAction($action, $deepCopyList, $parentModel);
        }
        unset($action);

        // When pasting after another model, apply same grouping information.
        $this->ensureSameGrouping($actions, $after);

        // Now apply sorting and persist all models.
        $models = $this->sortAndPersistModels($actions, $after, $into, $parentModelId, $items);

        // At least, go ahead with the deep copy.
        $this->doDeepCopy($deepCopyList);

        return $models;
    }

    /**
     * Apply the action onto the model.
     *
     * This will create or clone the model in the action.
     *
     * @param array               $action       The action, containing a model and an item.
     * @param array               $deepCopyList A list of models that need deep copy.
     * @param ModelInterface|null $parentModel  The parent model.
     *
     * @return void
     *
     */
    private function applyAction(array &$action, array &$deepCopyList, ModelInterface $parentModel = null)
    {
        /** @var ModelInterface|null $model */
        $model = $action['model'];
        /** @var ItemInterface $item */
        $item       = $action['item'];
        $isDeepCopy = false;

        if ($item->isCreate()) {
            // create new model
            $model = $this->createEmptyModelWithDefaults();
        } elseif ($item->isCopy() || $isDeepCopy = $item->isDeepCopy()) {
            assert($model instanceof ModelInterface);
            // copy model
            $model = $this->modelCollector->getModel(ModelId::fromModel($model));
            assert($model instanceof ModelInterface);
            $clonedModel = $this->doCloneAction($model);

            if ($isDeepCopy) {
                $deepCopyList[] = [
                    'origin' => $model,
                    'model'  => $clonedModel,
                ];
            }

            $model = $clonedModel;
        }

        if (!$model) {
            throw new \UnexpectedValueException(
                'Invalid clipboard action entry, no model created. ' . $item->getAction()
            );
        }

        if ($parentModel) {
            $this->relationshipManager->setParent($model, $parentModel);
        }

        $action['model'] = $model;
    }

    /**
     * Effectively do the clone action on the model.
     *
     * @param ModelInterface $model The model to clone.
     *
     * @return ModelInterface Return the cloned model.
     */
    private function doCloneAction(ModelInterface $model)
    {
        $environment = $this->getEnvironment();

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        // Make a duplicate.
        $clonedModel = $this->createClonedModel($model);

        // Trigger the pre duplicate event.
        $duplicateEvent = new PreDuplicateModelEvent($environment, $clonedModel, $model);
        $dispatcher->dispatch($duplicateEvent, $duplicateEvent::NAME);

        // And trigger the post event for it.
        $duplicateEvent = new PostDuplicateModelEvent($environment, $clonedModel, $model);
        $dispatcher->dispatch($duplicateEvent, $duplicateEvent::NAME);

        return $clonedModel;
    }

    /**
     * Ensure all models have the same grouping.
     *
     * @param array                 $actions The action's collection.
     * @param ModelIdInterface|null $after   The previous model id.
     *
     * @return void
     */
    private function ensureSameGrouping(array $actions, ModelIdInterface $after = null)
    {
        $environment  = $this->getEnvironment();
        $groupingMode = ViewHelpers::getGroupingMode($environment);
        if ($groupingMode && $after && $after->getId()) {
            // when pasting after another item, inherit the grouping field
            $groupingField = $groupingMode['property'];
            $previous      = $this->modelCollector->getModel($after);
            assert($previous instanceof ModelInterface);

            $groupingValue = $previous->getProperty($groupingField);

            foreach ($actions as $action) {
                /** @var ModelInterface $model */
                $model = $action['model'];
                $model->setProperty($groupingField, $groupingValue);
            }
        }
    }

    /**
     * Apply sorting and persist all models.
     *
     * @param array                 $actions       The actions collection.
     * @param ModelIdInterface|null $after         The previous model id.
     * @param ModelIdInterface|null $into          The hierarchical parent model id.
     * @param ModelIdInterface|null $parentModelId The parent model id.
     * @param array                 $items         Write-back clipboard items.
     *
     * @return DefaultCollection
     *
     */
    private function sortAndPersistModels(
        array $actions,
        ModelIdInterface $after = null,
        ModelIdInterface $into = null,
        ModelIdInterface $parentModelId = null,
        array &$items = []
    ) {
        $models = $this->createModelCollectionFromActions($actions, $items);
        assert($models instanceof CollectionInterface);

        $this->triggerPrePasteModel($models);

        $this->processPasteAfter($models, $after);
        $this->processPasteInto($models, $into);
        $this->processPasteTopWithoutReference($models, $after, $into, $parentModelId);
        $this->processPasteTopAfterModel($models, $parentModelId);

        if ($models->count()) {
            throw new DcGeneralRuntimeException('Invalid parameters.');
        }

        return $models;
    }

    /**
     * Process paste the collection of models after the a model.
     *
     * @param CollectionInterface   $models The collection of models.
     * @param ModelIdInterface|null $after  The paste after model.
     *
     * @return void
     */
    private function processPasteAfter(CollectionInterface $models, ModelIdInterface $after = null)
    {
        if ($after && $models->count() && $after->getId()) {
            $manualSorting = ViewHelpers::getManualSortingProperty($this->getEnvironment());
            assert(\is_string($manualSorting));

            $model = $this->modelCollector->getModel($after);
            assert($model instanceof ModelInterface);

            $this->pasteAfter($model, $models, $manualSorting);

            $this->triggerPostPasteModel($models);
            $this->clearModelCollection($models);
        }
    }

    /**
     * Process paste the collection of models into the model.
     *
     * @param CollectionInterface   $models The collection of models.
     * @param ModelIdInterface|null $into   The paste into model.
     *
     * @return void
     */
    private function processPasteInto(CollectionInterface $models, ModelIdInterface $into = null)
    {
        if ($into && $models->count() && $into->getId()) {
            $manualSorting = ViewHelpers::getManualSortingProperty($this->getEnvironment());
            assert(\is_string($manualSorting));

            $model = $this->modelCollector->getModel($into);
            assert($model instanceof ModelInterface);

            $this->pasteInto($model, $models, $manualSorting);

            $this->triggerPostPasteModel($models);
            $this->clearModelCollection($models);
        }
    }

    /**
     * Process paste the content of the clipboard onto the top after a model without reference.
     *
     * @param CollectionInterface   $models The collection of models.
     * @param ModelIdInterface|null $after  The previous model id.
     * @param ModelIdInterface|null $into   The hierarchical parent model id.
     * @param ModelIdInterface|null $parent The parent model id.
     *
     * @return void
     */
    private function processPasteTopWithoutReference(
        CollectionInterface $models,
        ModelIdInterface $after = null,
        ModelIdInterface $into = null,
        ModelIdInterface $parent = null
    ) {
        if (
            $models->count()
            && (($after && (0 === (int) $after->getId()))
                || ($into && (0 === (int) $into->getId())))
        ) {
            $manualSorting = ViewHelpers::getManualSortingProperty($this->getEnvironment());
            assert(\is_string($manualSorting));

            $dataDefinition = $this->getEnvironment()->getDataDefinition();
            assert($dataDefinition instanceof ContainerInterface);

            if (BasicDefinitionInterface::MODE_HIERARCHICAL === $dataDefinition->getBasicDefinition()->getMode()) {
                $this->relationshipManager->setAllRoot($models);
            }

            $this->pasteTop($models, $manualSorting, $parent);

            $this->triggerPostPasteModel($models);
            $this->clearModelCollection($models);
        }
    }

    /**
     * Process paste the content of the clipboard onto the top after a model.
     *
     * @param CollectionInterface $models The collection of models.
     * @param ModelIdInterface    $parent The parent model id.
     *
     * @return void
     */
    private function processPasteTopAfterModel(CollectionInterface $models, ModelIdInterface $parent = null)
    {
        if ($parent && $models->count()) {
            $manualSorting = ViewHelpers::getManualSortingProperty($this->getEnvironment());

            if ($manualSorting) {
                $this->pasteTop($models, $manualSorting, $parent);

                return;
            }

            $dataProvider = $this->getEnvironment()->getDataProvider();
            assert($dataProvider instanceof DataProviderInterface);

            $dataProvider->saveEach($models);

            $this->triggerPostPasteModel($models);
            $this->clearModelCollection($models);
        }
    }

    /**
     * Create the model collection from the internal models in the action collection.
     *
     * @param array $actions The actions collection.
     * @param array $items   Write-back clipboard items.
     *
     * @return DefaultCollection
     */
    private function createModelCollectionFromActions(array $actions, array &$items)
    {
        $models = new DefaultCollection();
        foreach ($actions as $action) {
            $models->push($action['model']);
            $items[] = $action['item'];
        }

        return $models;
    }

    /**
     * Trigger for each model the pre persist event.
     *
     * @param CollectionInterface $collection The collection of models.
     *
     * @return void
     */
    private function triggerPrePasteModel(CollectionInterface $collection)
    {
        foreach ($collection as $model) {
            $event = new PrePasteModelEvent($this->getEnvironment(), $model);

            $dispatcher = $this->getEnvironment()->getEventDispatcher();
            assert($dispatcher instanceof EventDispatcherInterface);

            $dispatcher->dispatch($event, $event::NAME);
        }
    }

    /**
     * Trigger for each model the past persist event.
     *
     * @param CollectionInterface $collection The collection of models.
     *
     * @return void
     */
    private function triggerPostPasteModel(CollectionInterface $collection)
    {
        foreach ($collection as $model) {
            $event = new PostPasteModelEvent($this->getEnvironment(), $model);

            $dispatcher = $this->getEnvironment()->getEventDispatcher();
            assert($dispatcher instanceof EventDispatcherInterface);

            $dispatcher->dispatch($event, $event::NAME);
        }
    }

    /**
     * Do deep copy.
     *
     * @param array $deepCopyList The deep copy list.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function doDeepCopy(array $deepCopyList)
    {
        if (empty($deepCopyList)) {
            return;
        }

        $factory = DcGeneralFactory::deriveFromEnvironment($this->getEnvironment());

        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $modelRelationshipDefinition = $dataDefinition->getModelRelationshipDefinition();
        $childConditions             = $modelRelationshipDefinition->getChildConditions($dataDefinition->getName());

        foreach ($deepCopyList as $deepCopy) {
            /** @var ModelInterface $origin */
            $origin = $deepCopy['origin'];
            /** @var ModelInterface $model */
            $model = $deepCopy['model'];

            $parentId = ModelId::fromModel($model);

            foreach ($childConditions as $childCondition) {
                // create new destination environment
                $destinationName = $childCondition->getDestinationName();
                $factory->setContainerName($destinationName);
                $destinationEnvironment = $factory->createEnvironment();

                $destinationDataDefinition = $destinationEnvironment->getDataDefinition();
                assert($destinationDataDefinition instanceof ContainerInterface);

                $destinationViewDefinition = $destinationDataDefinition->getDefinition(
                    Contao2BackendViewDefinitionInterface::NAME
                );
                assert($destinationViewDefinition instanceof Contao2BackendViewDefinitionInterface);

                $destinationDataProvider = $destinationEnvironment->getDataProvider();
                assert($destinationDataProvider instanceof DataProviderInterface);

                $destinationController = $destinationEnvironment->getController();
                assert($destinationController instanceof ControllerInterface);

                /** @var Contao2BackendViewDefinitionInterface $destinationViewDefinition */
                /** @var DefaultController $destinationController */
                $listingConfig             = $destinationViewDefinition->getListingConfig();
                $groupAndSortingCollection = $listingConfig->getGroupAndSortingDefinition();
                $groupAndSorting           = $groupAndSortingCollection->getDefault();

                // ***** fetch the children
                $filter = $childCondition->getFilter($origin);

                // apply parent-child condition
                $config = $destinationDataProvider->getEmptyConfig();
                $config->setFilter($filter);

                // apply sorting
                $sorting = [];
                foreach ($groupAndSorting as $information) {
                    /** @var GroupAndSortingInformationInterface $information */
                    $sorting[$information->getProperty()] = $information->getSortingMode();
                }
                $config->setSorting($sorting);

                // receive children
                $children = $destinationDataProvider->fetchAll($config);

                // ***** do the deep copy
                $actions = [];

                // build the copy actions
                foreach ($children as $childModel) {
                    if (!($childModel instanceof ModelInterface)) {
                        continue;
                    }

                    $childModelId = ModelId::fromModel($childModel);

                    $actions[] = [
                        'model' => $childModel,
                        'item'  => new Item(ItemInterface::DEEP_COPY, $parentId, $childModelId)
                    ];
                }

                // do the deep copy
                $childrenModels = $destinationController->doActions($actions, null, null, $parentId);

                // ensure parent-child condition
                foreach ($childrenModels as $childrenModel) {
                    $childCondition->applyTo($model, $childrenModel);
                }
                $destinationDataProvider->saveEach($childrenModels);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function pasteTop(CollectionInterface $models, $sortedBy, ModelIdInterface $parentId = null)
    {
        $environment = $this->getEnvironment();

        $firstModel = $models->get(0);
        assert($firstModel instanceof ModelInterface);

        // Enforce proper sorting now.
        $siblings = $this->modelCollector->collectSiblingsOf($firstModel, $sortedBy, $parentId);
        $newList  = (new SortingManager($models, $siblings, $sortedBy, null))->getResults();
        assert($newList instanceof CollectionInterface);

        $dataProvider = $environment->getDataProvider($firstModel->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $dataProvider->saveEach($newList);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException When no models have been passed.
     */
    public function pasteAfter(ModelInterface $previousModel, CollectionInterface $models, $sortedBy)
    {
        if (0 === $models->length()) {
            throw new \RuntimeException('No models passed to pasteAfter().');
        }

        $environment = $this->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (
            \in_array(
                $definition->getBasicDefinition()->getMode(),
                [
                    BasicDefinitionInterface::MODE_HIERARCHICAL,
                    BasicDefinitionInterface::MODE_PARENTEDLIST
                ]
            )
        ) {
            if (!$this->relationshipManager->isRoot($previousModel)) {
                $parentModel = $this->modelCollector->searchParentOf($previousModel);
                assert($parentModel instanceof ModelInterface);

                $parentName  = $parentModel->getProviderName();
                $this->relationshipManager->setSameParentForAll($models, $previousModel, $parentName);
            } else {
                $this->relationshipManager->setAllRoot($models);
            }
        }

        // Enforce proper sorting now.
        $siblings = $this->modelCollector->collectSiblingsOf($previousModel, $sortedBy);
        $newList  = (new SortingManager($models, $siblings, $sortedBy, $previousModel))->getResults();
        assert($newList instanceof CollectionInterface);

        $dataProvider = $environment->getDataProvider($previousModel->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $dataProvider->saveEach($newList);
    }

    /**
     * {@inheritDoc}
     */
    public function pasteInto(ModelInterface $parentModel, CollectionInterface $models, $sortedBy)
    {
        $environment = $this->getEnvironment();

        $this->relationshipManager->setParentForAll($models, $parentModel);

        // Enforce proper sorting now.
        $siblings = $this->assembleChildrenFor($parentModel, $sortedBy);
        $newList  = (new SortingManager($models, $siblings, $sortedBy))->getResults();
        assert($newList instanceof CollectionInterface);

        $firstItem = $newList->get(0);
        assert($firstItem instanceof ModelInterface);

        $dataProvider = $environment->getDataProvider($firstItem->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $dataProvider->saveEach($newList);
    }

    /**
     * Clear the collection of the models after insert.
     *
     * @param CollectionInterface $models The collection of models.
     *
     * @return void
     */
    private function clearModelCollection(CollectionInterface $models)
    {
        foreach ($models as $model) {
            $models->remove($model);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot()
     */
    public function isRootModel(ModelInterface $model)
    {
        return $this->relationshipManager->isRoot($model);
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot()
     */
    public function setRootModel(ModelInterface $model)
    {
        $this->relationshipManager->setRoot($model);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent()
     */
    public function setParent(ModelInterface $childModel, ModelInterface $parentModel)
    {
        $this->relationshipManager->setParent($childModel, $parentModel);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent()
     */
    public function setSameParent(ModelInterface $receivingModel, ModelInterface $sourceModel, $parentTable)
    {
        if ($this->relationshipManager->isRoot($sourceModel)) {
            $this->relationshipManager->setRoot($receivingModel);

            return $this;
        }

        $this->relationshipManager->setSameParent($receivingModel, $sourceModel, $parentTable);

        return $this;
    }
}
