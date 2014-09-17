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
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
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
     * @param array  $arguments Method arguments.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException Always.
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
        $this->getEnvironment()->getEventPropagator()->propagate(
            DcGeneralEvents::ACTION,
            $event,
            array
            (
                $this->getEnvironment()->getDataDefinition()->getName(),
                $action->getName()
            )
        );
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
            $config->setSorting(array((string)$sortingProperty => 'ASC'));
        }

        // Handle grouping.
        /** @var Contao2BackendViewDefinitionInterface $backendViewDefinition */
        // TODO TL dnk how to handle this without highjacking the view.
        $backendViewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        if ($backendViewDefinition && $backendViewDefinition instanceof Contao2BackendViewDefinitionInterface) {
            $listingConfig        = $backendViewDefinition->getListingConfig();
            $sortingProperties    = array_keys((array)$listingConfig->getDefaultSortingFields());
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
            $config->setSorting(array((string)$sortingProperty => 'ASC'));
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
            $objLanguagesSupported = $objDataProvider->getLanguages($mixID);
        } else {
            $objLanguagesSupported = null;
        }

        // Check if we have some languages.
        if ($objLanguagesSupported == null) {
            return array();
        }

        // Make an array from the collection.
        $arrLanguage = array();
        $translator  = $environment->getTranslator();
        foreach ($objLanguagesSupported as $value) {
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
    public function fetchModelFromProvider($id, $providerName = null)
    {
        if ($providerName === null) {
            if (is_string($id)) {
                $id = IdSerializer::fromSerialized($id);
            }
        } else {
            $id = IdSerializer::fromValues($providerName, $id);
        }

        $dataProvider = $this->getEnvironment()->getDataProvider($id->getDataProviderName());
        $item         = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($id->getId()));

        return $item;
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
