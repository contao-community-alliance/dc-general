<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class serves as main controller class in dc general.
 *
 * It holds various methods for data manipulation and retrieval that is non view related.
 */
class DefaultController implements ControllerInterface
{

    /**
     * The attached environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * A list with all current IDs.
     *
     * @var array
     */
    protected $arrInsertIDs = array();

    /**
     * Error message.
     *
     * @var string
     */
    protected $notImplMsg =
        '<divstyle="text-align:center; font-weight:bold; padding:40px;">
        The function/view &quot;%s&quot; is not implemented.<br />Please
        <a
            target="_blank"
            style="text-decoration:underline"
            href="https://github.com/contao-community-alliance/dc-general/issues">support us</a>
        to add this important feature!</div>';

    /**
     * Field for the function sortCollection.
     *
     * @var string $arrColSort
     */
    protected $arrColSort;

    /**
     * Throw an exception that an unknown method has been called.
     *
     * @param string $name      Method name.
     *
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
        // FIXME can we stop propagate with eventName[x][y] syntax here?
        $this->getEnvironment()->getEventDispatcher()->dispatch(
            sprintf(
                '%s[%s][%s]',
                DcGeneralEvents::ACTION,
                $this->getEnvironment()->getDataDefinition()->getName(),
                $action->getName()
            ),
            $event
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', DcGeneralEvents::ACTION, $this->getEnvironment()->getDataDefinition()->getName()),
            $event
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(DcGeneralEvents::ACTION, $event);

        return $event->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function searchParentOfIn(ModelInterface $model, CollectionInterface $models)
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $relationships = $definition->getModelRelationshipDefinition();

        foreach ($models as $candidate) {
            /** @var ModelInterface $candidate */
            foreach ($relationships->getChildConditions($candidate->getProviderName()) as $condition) {
                if ($condition->matches($candidate, $model)) {
                    return $candidate;
                }

                $provider = $environment->getDataProvider($condition->getDestinationName());
                $config   = $provider
                    ->getEmptyConfig()
                    ->setFilter($condition->getFilter($candidate));

                $result = $this->searchParentOfIn($model, $provider->fetchAll($config));
                if ($result === true) {
                    return $candidate;
                } elseif ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a root model has been passed or not in hierarchical mode.
     */
    public function searchParentOf(ModelInterface $model)
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $relationships = $definition->getModelRelationshipDefinition();
        $mode          = $definition->getBasicDefinition()->getMode();

        if ($mode === BasicDefinitionInterface::MODE_HIERARCHICAL) {
            if ($this->isRootModel($model)) {
                throw new DcGeneralInvalidArgumentException('Invalid condition, root models can not have parents!');
            }
            // To speed up, some conditions have an inverse filter - we should use them!
            // Start from the root data provider and walk through the whole tree.
            $provider  = $environment->getDataProvider($definition->getBasicDefinition()->getRootDataProvider());
            $condition = $relationships->getRootCondition();
            $config    = $provider->getEmptyConfig()->setFilter($condition->getFilterArray());

            return $this->searchParentOfIn($model, $provider->fetchAll($config));
        } elseif ($mode === BasicDefinitionInterface::MODE_PARENTEDLIST) {
            $provider  = $environment->getDataProvider($definition->getBasicDefinition()->getParentDataProvider());
            $condition = $relationships->getChildCondition(
                $definition->getBasicDefinition()->getParentDataProvider(),
                $definition->getBasicDefinition()->getDataProvider()
            );
            $config    = $provider->getEmptyConfig();
            // This is pretty expensive, we fetch all models from the parent provider here.
            // This can be much faster by using the inverse condition if present.
            foreach ($provider->fetchAll($config) as $candidate) {
                if ($condition->matches($candidate, $model)) {
                    return $candidate;
                }
            }

            return null;
        }

        throw new DcGeneralInvalidArgumentException('Invalid condition, not in hierarchical mode!');
    }

    /**
     * {@inheritDoc}
     */
    public function assembleAllChildrenFrom($objModel, $strDataProvider = '')
    {
        if ($strDataProvider == '') {
            $strDataProvider = $objModel->getProviderName();
        }

        $arrIds = array();

        if ($strDataProvider == $objModel->getProviderName()) {
            $arrIds = array($objModel->getId());
        }

        // Check all data providers for children of the given element.
        $conditions = $this
            ->getEnvironment()
            ->getDataDefinition()
            ->getModelRelationshipDefinition()
            ->getChildConditions($objModel->getProviderName());
        foreach ($conditions as $objChildCondition) {
            $objDataProv = $this->getEnvironment()->getDataProvider($objChildCondition->getDestinationName());
            $objConfig   = $objDataProv->getEmptyConfig();
            $objConfig->setFilter($objChildCondition->getFilter($objModel));

            foreach ($objDataProv->fetchAll($objConfig) as $objChild) {
                /** @var ModelInterface $objChild */
                if ($strDataProvider == $objChild->getProviderName()) {
                    $arrIds[] = $objChild->getId();
                }

                $arrIds = array_merge($arrIds, $this->assembleAllChildrenFrom($objChild, $strDataProvider));
            }
        }

        return $arrIds;
    }

    /**
     * Retrieve all siblings of a given model.
     *
     * @param ModelInterface $model           The model for which the siblings shall be retrieved from.
     *
     * @param string|null    $sortingProperty The property name to use for sorting.
     *
     * @return CollectionInterface
     *
     * @throws DcGeneralRuntimeException When no parent model can be located.
     *
     * @todo This might return a lot of models, we definately want to use some lazy approach rather than this.
     */
    protected function assembleSiblingsFor(ModelInterface $model, $sortingProperty = null)
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $provider      = $environment->getDataProvider($model->getProviderName());
        $config        = $this->getBaseConfig();
        $relationships = $definition->getModelRelationshipDefinition();

        // Root model in hierarchical mode?
        if ($this->isRootModel($model)) {
            $condition = $relationships->getRootCondition();

            if ($condition) {
                $config->setFilter($condition->getFilterArray());
            }
        } elseif ($definition->getBasicDefinition()->getMode() === BasicDefinitionInterface::MODE_HIERARCHICAL) {
            // Are we at least in hierarchical mode?
            $parent = $this->searchParentOf($model);

            if (!$parent instanceof ModelInterface) {
                throw new DcGeneralRuntimeException(
                    'Parent could not be found, are the parent child conditions correct?'
                );
            }

            $condition = $relationships->getChildCondition($parent->getProviderName(), $model->getProviderName());
            $config->setFilter($condition->getFilter($parent));
        }

        if ($sortingProperty) {
            $config->setSorting(array((string) $sortingProperty => 'ASC'));
        }

        // Handle grouping.
        /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
        // TODO TL dnk how to handle this without highjacking the view.
        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        if ($viewDefinition && $viewDefinition instanceof Contao2BackendViewDefinitionInterface) {
            $listingConfig        = $viewDefinition->getListingConfig();
            $sortingProperties    = array_keys((array) $listingConfig->getDefaultSortingFields());
            $sortingPropertyIndex = array_search($sortingProperty, $sortingProperties);

            if ($sortingPropertyIndex !== false && $sortingPropertyIndex > 0) {
                $sortingProperties = array_slice($sortingProperties, 0, $sortingPropertyIndex);
                $filters           = $config->getFilter();

                foreach ($sortingProperties as $propertyName) {
                    $filters[] = array(
                        'operation' => '=',
                        'property'  => $propertyName,
                        'value'     => $model->getProperty($propertyName),
                    );
                }

                $config->setFilter($filters);
            }
        }

        $siblings = $provider->fetchAll($config);

        return $siblings;
    }

    /**
     * Retrieve children of a given model.
     *
     * @param ModelInterface $model           The model for which the children shall be retrieved.
     *
     * @param string|null    $sortingProperty The property name to use for sorting.
     *
     * @return CollectionInterface
     *
     * @throws DcGeneralRuntimeException When not in hierarchical mode.
     */
    protected function assembleChildrenFor(ModelInterface $model, $sortingProperty = null)
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $provider      = $environment->getDataProvider($model->getProviderName());
        $config        = $this->getBaseConfig();
        $relationships = $definition->getModelRelationshipDefinition();

        if ($definition->getBasicDefinition()->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            throw new DcGeneralRuntimeException('Unable to retrieve children in non hierarchical mode.');
        }

        $condition = $relationships->getChildCondition($model->getProviderName(), $model->getProviderName());
        $config->setFilter($condition->getFilter($model));

        if ($sortingProperty) {
            $config->setSorting(array((string) $sortingProperty => 'ASC'));
        }

        $siblings = $provider->fetchAll($config);

        return $siblings;
    }

    /**
     * {@inheritDoc}
     */
    public function updateModelFromPropertyBag($model, $propertyValues)
    {
        if (!$propertyValues) {
            return $this;
        }
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();

        foreach ($propertyValues as $property => $value) {
            try {
                $model->setProperty($property, $value);
                $model->setMeta($model::IS_CHANGED, true);
            } catch (\Exception $exception) {
                $propertyValues->markPropertyValueAsInvalid($property, $exception->getMessage());
            }
        }

        $basicDefinition = $environment->getDataDefinition()->getBasicDefinition();

        if (($basicDefinition->getMode() & (
                    BasicDefinitionInterface::MODE_PARENTEDLIST
                    | BasicDefinitionInterface::MODE_HIERARCHICAL)
            )
            && ($input->hasParameter('pid'))
        ) {
            $parentModelId      = IdSerializer::fromSerialized($input->getParameter('pid'));
            $providerName       = $basicDefinition->getDataProvider();
            $parentProviderName = $parentModelId->getDataProviderName();
            $objParentDriver    = $environment->getDataProvider($parentProviderName);
            $objParentModel     = $objParentDriver->fetch(
                $objParentDriver
                    ->getEmptyConfig()
                    ->setId($parentModelId->getId())
            );

            $relationship = $environment
                ->getDataDefinition()
                ->getModelRelationshipDefinition()
                ->getChildCondition($parentProviderName, $providerName);

            if ($relationship && $relationship->getSetters()) {
                $relationship->applyTo($objParentModel, $model);
            }
        }

        return $this;
    }

    /**
     * Add the filter for the item with the given id from the parent data provider to the given config.
     *
     * @param mixed           $idParent The id of the parent item.
     *
     * @param ConfigInterface $config   The config to add the filter to.
     *
     * @return ConfigInterface
     *
     * @throws DcGeneralRuntimeException When the parent item is not found.
     */
    protected function addParentFilter($idParent, $config)
    {
        $environment        = $this->getEnvironment();
        $definition         = $environment->getDataDefinition();
        $providerName       = $definition->getBasicDefinition()->getDataProvider();
        $parentProviderName = $definition->getBasicDefinition()->getParentDataProvider();
        $parentProvider     = $environment->getDataProvider($parentProviderName);

        if ($parentProvider) {
            $objParent = $parentProvider->fetch($parentProvider->getEmptyConfig()->setId($idParent));
            if (!$objParent) {
                throw new DcGeneralRuntimeException(
                    'Parent item ' . $idParent . ' not found in ' . $parentProviderName
                );
            }

            $condition = $definition->getModelRelationshipDefinition()->getChildCondition(
                $parentProviderName,
                $providerName
            );

            if ($condition) {
                $arrBaseFilter = $config->getFilter();
                $arrFilter     = $condition->getFilter($objParent);

                if ($arrBaseFilter) {
                    $arrFilter = array_merge($arrBaseFilter, $arrFilter);
                }

                $config->setFilter(
                    array(
                        array(
                            'operation' => 'AND',
                            'children'  => $arrFilter,
                        )
                    )
                );
            }
        }

        return $config;
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
        $environment     = $this->getEnvironment();
        $objDataProvider = $environment->getDataProvider();

        // Check if current data provider supports multi language.
        if (in_array(
            'ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface',
            class_implements($objDataProvider)
        )
        ) {
            /** @var MultiLanguageDataProviderInterface $objDataProvider */
            $supportedLanguages = $objDataProvider->getLanguages($mixID);
        } else {
            $supportedLanguages = null;
        }

        // Check if we have some languages.
        if ($supportedLanguages == null) {
            return array();
        }

        // Make an array from the collection.
        $arrLanguage = array();
        $translator  = $environment->getTranslator();
        foreach ($supportedLanguages as $value) {
            /** @var LanguageInformationInterface $value */
            $arrLanguage[$value->getLocale()] = $translator->translate('LNG.' . $value->getLocale(), 'languages');
        }

        return $arrLanguage;
    }

    /**
     * Retrieve the base data provider config for the current data definition.
     *
     * This includes parent filter when in parented list mode and the additional filters from the data definition.
     *
     * @return ConfigInterface
     */
    public function getBaseConfig()
    {
        $environment   = $this->getEnvironment();
        $objConfig     = $environment->getDataProvider()->getEmptyConfig();
        $objDefinition = $environment->getDataDefinition();
        $arrAdditional = $objDefinition->getBasicDefinition()->getAdditionalFilter();

        // Custom filter common for all modes.
        if ($arrAdditional) {
            $objConfig->setFilter($arrAdditional);
        }

        if (!$objConfig->getSorting()) {
            /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
            $viewDefinition = $objDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            $objConfig->setSorting($viewDefinition->getListingConfig()->getDefaultSortingFields());
        }

        // Special filter for certain modes.
        if ($objDefinition->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_PARENTEDLIST) {
            $pid        = $environment->getInputProvider()->getParameter('pid');
            $pidDetails = IdSerializer::fromSerialized($pid);

            $this->addParentFilter($pidDetails->getId(), $objConfig);
        }

        return $objConfig;
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
        $properties  = $environment->getDataDefinition()->getPropertiesDefinition();

        foreach (array_keys($clone->getPropertiesAsArray()) as $propName) {
            $property = $properties->getProperty($propName);

            // If the property is not known, remove it.
            if (!$property) {
                continue;
            }

            $extra = $property->getExtra();

            // Check doNotCopy.
            if (isset($extra['doNotCopy']) && $extra['doNotCopy'] === true) {
                $clone->setProperty($propName, null);
                continue;
            }

            $dataProvider = $environment->getDataProvider($clone->getProviderName());

            // Check fallback.
            if (isset($extra['fallback']) && $extra['fallback'] === true) {
                $dataProvider->resetFallback($propName);
            }

            // Check uniqueness.
            if (isset($extra['unique'])
                && $extra['unique'] === true
                && !$dataProvider->isUniqueValue($propName, $clone->getProperty($propName))
            ) {
                // Implicit "do not copy" unique values, they cannot be unique anymore.
                $clone->setProperty($propName, null);
            }
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchModelFromProvider($modelId, $providerName = null)
    {
        if ($providerName === null) {
            if (is_string($modelId)) {
                $modelId = IdSerializer::fromSerialized($modelId);
            }
        } else {
            $modelId = IdSerializer::fromValues($providerName, $modelId);
        }

        $dataProvider = $this->getEnvironment()->getDataProvider($modelId->getDataProviderName());
        $item         = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    public function createEmptyModelWithDefaults()
    {
        $environment        = $this->getEnvironment();
        $definition         = $environment->getDataDefinition();
        $dataProvider       = $environment->getDataProvider();
        $propertyDefinition = $definition->getPropertiesDefinition();
        $properties         = $propertyDefinition->getProperties();
        $model              = $dataProvider->getEmptyModel();

        foreach ($properties as $property) {
            $propName = $property->getName();

            if ($property->getDefaultValue() !== null) {
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
        $modelId      = $item->getModelId();
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $config       = $dataProvider->getEmptyConfig()->setId($modelId->getId());
        $model        = $dataProvider->fetch($config);

        return $model;
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
            $modelId      = $item->getModelId();
            $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
            $config       = $dataProvider->getEmptyConfig()->setId($modelId->getId());
            $model        = $dataProvider->fetch($config);
            $models->push($model);
        }

        return $models;
    }

    /**
     * {@inheritDoc}
     */
    public function getModelsFromClipboard(IdSerializer $parentModelId = null)
    {
        $environment       = $this->getEnvironment();
        $dataDefinition    = $environment->getDataDefinition();
        $basicDefinition   = $dataDefinition->getBasicDefinition();
        $modelProviderName = $basicDefinition->getDataProvider();
        $clipboard         = $environment->getClipboard();

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
        IdSerializer $source = null,
        IdSerializer $after = null,
        IdSerializer $into = null,
        IdSerializer $parentModelId = null,
        FilterInterface $filter = null,
        array &$items = array()
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
     * @param IdSerializer      $source        The source id.
     * @param IdSerializer|null $parentModelId The parent id.
     *
     * @return array
     */
    private function getActionsFromSource(IdSerializer $source, IdSerializer $parentModelId = null)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($source->getDataProviderName());

        $filterConfig = $dataProvider->getEmptyConfig();
        $filterConfig->setId($source->getId());

        $model   = $dataProvider->fetch($filterConfig);
        $modelId = IdSerializer::fromModel($model);
        $item    = new Item(ItemInterface::CUT, $parentModelId, $modelId);

        $actions = array(
            array(
                'model' => $model,
                'item'  => $item,
            )
        );

        return $actions;
    }

    /**
     * Fetch actions from the clipboard.
     *
     * @param FilterInterface|null $filter        The clipboard filter.
     * @param IdSerializer         $parentModelId The parent id.
     *
     * @return array
     */
    private function fetchModelsFromClipboard(FilterInterface $filter = null, IdSerializer $parentModelId = null)
    {
        $environment    = $this->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();

        if (!$filter) {
            $filter = new Filter();
        }

        $basicDefinition   = $dataDefinition->getBasicDefinition();
        $modelProviderName = $basicDefinition->getDataProvider();
        $filter->andModelIsFromProvider($modelProviderName);
        if ($parentModelId) {
            $filter->andParentIsFromProvider($parentModelId->getDataProviderName());
        } else {
            $filter->andHasNoParent();
        }

        $environment = $this->getEnvironment();
        $clipboard   = $environment->getClipboard();
        $items       = $clipboard->fetch($filter);
        $actions     = array();

        foreach ($items as $index => $item) {
            if ($item->isCreate()) {
                $model = null;
            } else {
                $modelId      = $item->getModelId();
                $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
                $config       = $dataProvider->getEmptyConfig()->setId($modelId->getId());
                $model        = $dataProvider->fetch($config);
            }

            $actions[] = array(
                'model' => $model,
                'item'  => $item,
            );
        }

        return $actions;
    }

    /**
     * Effectively do the actions.
     *
     * @param array        $actions       The actions collection.
     * @param IdSerializer $after         The previous model id.
     * @param IdSerializer $into          The hierarchical parent model id.
     * @param IdSerializer $parentModelId The parent model id.
     * @param array        $items         Write-back clipboard items.
     *
     * @return mixed
     */
    private function doActions(
        array $actions,
        IdSerializer $after = null,
        IdSerializer $into = null,
        IdSerializer $parentModelId = null,
        array &$items = array()
    ) {
        $environment = $this->getEnvironment();

        if ($parentModelId) {
            $dataProvider = $environment->getDataProvider($parentModelId->getDataProviderName());
            $config       = $dataProvider->getEmptyConfig()->setId($parentModelId->getId());
            $parentModel  = $dataProvider->fetch($config);
        } else {
            $parentModel = null;
        }

        // Holds models, that need deep-copy
        $deepCopyList = array();

        // Apply create and copy actions
        foreach ($actions as &$action) {
            $this->applyAction($action, $deepCopyList, $parentModel);
        }

        // When pasting after another model, apply same grouping informations
        // TODO to be discussed, this allow cut&paste over groupings with custom sorting
        $this->ensureSameGrouping($actions, $after);

        // Now apply sorting and persist all models
        $models = $this->sortAndPersistModels($actions, $after, $into, $parentModelId, $items);

        // At least, go ahead with the deep copy
        $this->doDeepCopy($deepCopyList);

        return $models;
    }

    /**
     * Apply the action onto the model.
     *
     * This will create or clone the model in the action.
     *
     * @param array          $action       The action, containing a model and an item.
     * @param array          $deepCopyList A list of models that need deep copy.
     * @param ModelInterface $parentModel  The parent model.
     *
     * @return void
     */
    private function applyAction(array &$action, array &$deepCopyList, ModelInterface $parentModel = null)
    {
        $environment = $this->getEnvironment();

        /** @var ModelInterface|null $model */
        $model = $action['model'];
        /** @var ItemInterface $item */
        $item = $action['item'];

        if ($item->isCreate()) {
            // create new model
            $model = $this->createEmptyModelWithDefaults();
        } elseif ($item->isCopy() || $item->isDeepCopy()) {
            // copy model
            $modelId      = IdSerializer::fromModel($model);
            $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
            $config       = $dataProvider->getEmptyConfig()->setId($modelId->getId());
            $model        = $dataProvider->fetch($config);

            $clonedModel = $this->doCloneAction($model);

            if ($item->isDeepCopy()) {
                $deepCopyList[] = array(
                    'origin' => $model,
                    'model'  => $clonedModel,
                );
            }

            $model = $clonedModel;
        }

        if ($parentModel) {
            $this->setParent($model, $parentModel);
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

        // Trigger the pre duplicate event.
        $duplicateEvent = new PreDuplicateModelEvent($environment, $model);

        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $duplicateEvent::NAME, $environment->getDataDefinition()->getName()),
            $duplicateEvent
        );
        $environment->getEventDispatcher()->dispatch($duplicateEvent::NAME, $duplicateEvent);

        // Make a duplicate.
        $clonedModel = $this->createClonedModel($model);

        // And trigger the post event for it.
        $duplicateEvent = new PostDuplicateModelEvent($environment, $clonedModel, $model);
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $duplicateEvent::NAME, $environment->getDataDefinition()->getName()),
            $duplicateEvent
        );
        $environment->getEventDispatcher()->dispatch($duplicateEvent::NAME, $duplicateEvent);

        return $clonedModel;
    }

    /**
     * Ensure all models have the same grouping.
     *
     * @param array        $actions The actions collection.
     * @param IdSerializer $after   The previous model id.
     *
     * @return void
     */
    private function ensureSameGrouping(array $actions, IdSerializer $after = null)
    {
        $environment  = $this->getEnvironment();
        $groupingMode = ViewHelpers::getGroupingMode($environment);
        if ($groupingMode && $after) {
            // when pasting after another item, inherit the grouping field
            $groupingField = $groupingMode['property'];
            $dataProvider  = $environment->getDataProvider($after->getDataProviderName());
            $previous      = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($after->getId()));
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
     * @param array        $actions       The actions collection.
     * @param IdSerializer $after         The previous model id.
     * @param IdSerializer $into          The hierarchical parent model id.
     * @param IdSerializer $parentModelId The parent model id.
     * @param array        $items         Write-back clipboard items.
     *
     * @return DefaultCollection|ModelInterface[]
     */
    private function sortAndPersistModels(
        array $actions,
        IdSerializer $after = null,
        IdSerializer $into = null,
        IdSerializer $parentModelId = null,
        array &$items = array()
    ) {
        $environment    = $this->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        $manualSorting  = ViewHelpers::getManualSortingProperty($environment);

        /** @var DefaultCollection|ModelInterface[] $models */
        $models = new DefaultCollection();
        foreach ($actions as $action) {
            $models->push($action['model']);
            $items[] = $action['item'];
        }

        // Trigger for each model the pre persist event.
        foreach ($models as $model) {
            $event = new PrePasteModelEvent($environment, $model);

            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $event::NAME, $dataDefinition->getName()),
                $event
            );
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);
        }

        // FIXME too many parameters, this is just crazy! :-(

        if ($after && $after->getId()) {
            $dataProvider = $environment->getDataProvider($after->getDataProviderName());
            $previous     = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($after->getId()));
            $this->pasteAfter($previous, $models, $manualSorting);
        } elseif ($into && $into->getId()) {
            $dataProvider = $environment->getDataProvider($into->getDataProviderName());
            $parent       = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($into->getId()));
            $this->pasteInto($parent, $models, $manualSorting);
        } elseif (($after && $after->getId() == '0') || ($into && $into->getId() == '0')) {
            $this->pasteTop($models, $manualSorting);
        } elseif ($parentModelId) {
            if ($manualSorting) {
                $this->pasteTop($models, $manualSorting);
            } else {
                $dataProvider = $environment->getDataProvider();
                $dataProvider->saveEach($models);
            }
        } else {
            throw new DcGeneralRuntimeException('Invalid parameters.');
        }

        // Trigger for each model the past persist event.
        foreach ($models as $model) {
            $event = new PostPasteModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $event::NAME, $dataDefinition->getName()),
                $event
            );
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);
        }

        return $models;
    }

    /**
     * Do deep copy.
     *
     * @param array $deepCopyList The deep copy list.
     *
     * @return void
     */
    public function doDeepCopy(array $deepCopyList)
    {
        if (empty($deepCopyList)) {
            return;
        }

        // FIXME implement
        throw new \RuntimeException('Not implemented yet');

        foreach ($deepCopyList as $deepCopy) {
            /** @var ModelInterface $origin */
            $origin = $deepCopy['origin'];
            /** @var ModelInterface $origin */
            $model = $deepCopy['model'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function pasteTop(CollectionInterface $models, $sortedBy)
    {
        $environment = $this->getEnvironment();

        // Enforce proper sorting now.
        $siblings    = $this->assembleSiblingsFor($models->get(0), $sortedBy);
        $sortManager = new SortingManager($models, $siblings, $sortedBy, null);
        $newList     = $sortManager->getResults();

        $environment->getDataProvider($models->get(0)->getProviderName())->saveEach($newList);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException When no models have been passed.
     */
    public function pasteAfter(ModelInterface $previousModel, CollectionInterface $models, $sortedBy)
    {
        if ($models->length() == 0) {
            throw new \RuntimeException('No models passed to pasteAfter().');
        }
        $environment = $this->getEnvironment();

        if (in_array(
            $environment
                ->getDataDefinition()
                ->getBasicDefinition()
                ->getMode(),
            array
            (
                BasicDefinitionInterface::MODE_HIERARCHICAL,
                BasicDefinitionInterface::MODE_PARENTEDLIST
            )
        )) {
            $parentModel = null;
            $parentModel = null;

            if (!$this->isRootModel($previousModel)) {
                $parentModel = $this->searchParentOf($previousModel);
            }

            foreach ($models as $model) {
                /** @var ModelInterface $model */
                $this->setSameParent($model, $previousModel, $parentModel ? $parentModel->getProviderName() : null);
            }
        }

        // Enforce proper sorting now.
        $siblings    = $this->assembleSiblingsFor($previousModel, $sortedBy);
        $sortManager = new SortingManager($models, $siblings, $sortedBy, $previousModel);
        $newList     = $sortManager->getResults();

        $environment->getDataProvider($previousModel->getProviderName())->saveEach($newList);
    }

    /**
     * {@inheritDoc}
     */
    public function pasteInto(ModelInterface $parentModel, CollectionInterface $models, $sortedBy)
    {
        $environment = $this->getEnvironment();

        foreach ($models as $model) {
            $this->setParent($model, $parentModel);
        }

        // Enforce proper sorting now.
        $siblings    = $this->assembleChildrenFor($parentModel, $sortedBy);
        $sortManager = new SortingManager($models, $siblings, $sortedBy);
        $newList     = $sortManager->getResults();

        $environment->getDataProvider($newList->get(0)->getProviderName())->saveEach($newList);
    }

    /**
     * {@inheritDoc}
     */
    public function isRootModel(ModelInterface $model)
    {
        if ($this
                ->getEnvironment()
                ->getDataDefinition()
                ->getBasicDefinition()
                ->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL
        ) {
            return false;
        }

        return $this
            ->getEnvironment()
            ->getDataDefinition()
            ->getModelRelationshipDefinition()
            ->getRootCondition()
            ->matches($model);
    }

    /**
     * {@inheritDoc}
     */
    public function setRootModel(ModelInterface $model)
    {
        $rootCondition = $this
            ->getEnvironment()
            ->getDataDefinition()
            ->getModelRelationshipDefinition()
            ->getRootCondition();

        $rootCondition->applyTo($model);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParent(ModelInterface $childModel, ModelInterface $parentModel)
    {
        $this
            ->getEnvironment()
            ->getDataDefinition($childModel->getProviderName())
            ->getModelRelationshipDefinition()
            ->getChildCondition($parentModel->getProviderName(), $childModel->getProviderName())
            ->applyTo($parentModel, $childModel);
    }

    /**
     * {@inheritDoc}
     */
    public function setSameParent(ModelInterface $receivingModel, ModelInterface $sourceModel, $parentTable)
    {
        if ($this->isRootModel($sourceModel)) {
            $this->setRootModel($receivingModel);
        } else {
            $this
                ->getEnvironment()
                ->getDataDefinition()
                ->getModelRelationshipDefinition()
                ->getChildCondition($parentTable, $receivingModel->getProviderName())
                ->copyFrom($sourceModel, $receivingModel);
        }
    }
}
