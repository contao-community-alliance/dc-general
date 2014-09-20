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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Palette\LegacyPalettesParser;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSubmitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Build the container config from legacy DCA syntax.
 */
class LegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    const PRIORITY = 100;

    /**
     * {@inheritDoc}
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
    {
        if (!$this->loadDca($container->getName(), $this->getDispatcher())) {
            return;
        }

        $this->parseCallbacks($container, $this->getDispatcher());
        $this->parseBasicDefinition($container);
        $this->parseDataProvider($container);
        $this->parseRootEntries($container);
        $this->parseParentChildConditions($container);
        $this->parseBackendView($container);
        $this->parsePalettes($container);
        $this->parseProperties($container);
        $this->loadAdditionalDefinitions($container, $event);
    }

    /**
     * Load additional definitions, like naming of parent data provider.
     *
     * This method will register an event to the populate environment event in which the parent data provider container
     * will get loaded.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function loadAdditionalDefinitions(ContainerInterface $container)
    {
        if ($this->getFromDca('config/ptable')) {
            $this->getDispatcher()->addListener(
                sprintf('%s[%s]', PopulateEnvironmentEvent::NAME, $container->getName()),
                function (PopulateEnvironmentEvent $event) {
                    $environment      = $event->getEnvironment();
                    $definition       = $environment->getDataDefinition();
                    $parentName       = $definition->getBasicDefinition()->getParentDataProvider();
                    $factory          = DcGeneralFactory::deriveEmptyFromEnvironment($environment)->setContainerName(
                        $parentName
                    );
                    $parentDefinition = $factory->createContainer();

                    $environment->setParentDataDefinition($parentDefinition);
                }
            );
        }
    }

    /**
     * Register the callback handlers for the given legacy callbacks.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @param array                    $callbacks  The callbacks to be handled.
     *
     * @param string                   $eventName  The event to be registered to.
     *
     * @param array                    $suffixes   The suffixes for the event.
     *
     * @param string                   $listener   The listener class to use.
     *
     * @return void
     */
    protected function parseCallback($dispatcher, $callbacks, $eventName, $suffixes, $listener)
    {
        if (!(is_array($callbacks) || is_callable($callbacks))) {
            return;
        }

        // If only one callback given, ensure the loop below handles it correctly.
        if (is_array($callbacks) && (count($callbacks) == 2) && !is_array($callbacks[0])) {
            $callbacks = array($callbacks);
        }

        foreach ($suffixes as $suffix) {
            $eventName .= sprintf('[%s]', $suffix);
        }

        foreach ((array)$callbacks as $callback) {
            $dispatcher->addListener(
                $eventName,
                new $listener($callback)
            );
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param ContainerInterface       $container  The container where the data shall be stored.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher in use.
     *
     * @return void
     */
    protected function parsePropertyCallbacks(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        foreach ((array)$this->getFromDca('fields') as $propName => $propInfo) {

            if (isset($propInfo['load_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    $propInfo['load_callback'],
                    DecodePropertyValueForWidgetEvent::NAME,
                    array($container->getName(), $propName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnLoadCallbackListener'
                );
            }

            if (isset($propInfo['save_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    $propInfo['save_callback'],
                    EncodePropertyValueFromWidgetEvent::NAME,
                    array($container->getName(), $propName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnSaveCallbackListener'
                );
            }

            if (isset($propInfo['options_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    $propInfo['options_callback'],
                    GetPropertyOptionsEvent::NAME,
                    array($container->getName(), $propName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOptionsCallbackListener'
                );
            }

            if (isset($propInfo['input_field_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    $propInfo['input_field_callback'],
                    BuildWidgetEvent::NAME,
                    array($container->getName(), $propName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldCallbackListener'
                );
            }

            if (isset($propInfo['wizard'])) {
                $this->parseCallback(
                    $dispatcher,
                    $propInfo['wizard'],
                    ManipulateWidgetEvent::NAME,
                    array($container->getName(), $propName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldGetWizardCallbackListener'
                );
            }
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param ContainerInterface       $container  The container where the data shall be stored.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher in use.
     *
     * @return void
     */
    protected function parseCallbacks(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('config/onload_callback'),
            CreateDcGeneralEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('config/onsubmit_callback'),
            PostPersistModelEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('config/ondelete_callback'),
            PostDeleteModelEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('config/oncut_callback'),
            PostPasteModelEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('config/oncopy_callback'),
            PostDuplicateModelEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/sorting/header_callback'),
            GetParentHeaderEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/sorting/paste_button_callback'),
            GetPasteRootButtonEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/sorting/paste_button_callback'),
            GetPasteButtonEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/sorting/child_record_callback'),
            ParentViewChildRecordEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/label/group_callback'),
            GetGroupHeaderEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener'
        );

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/label/label_callback'),
            ModelToLabelEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener'
        );

        foreach ((array)$this->getFromDca('global_operations') as $operationName => $operationInfo) {
            if (isset($operationInfo['button_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    array($operationInfo['button_callback']),
                    GetGlobalButtonEvent::NAME,
                    array($container->getName(), $operationName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener'
                );
            }
        }

        foreach ((array)$this->getFromDca('list/operations') as $operationName => $operationInfo) {
            if (isset($operationInfo['button_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    array($operationInfo['button_callback']),
                    GetOperationButtonEvent::NAME,
                    array($container->getName(), $operationName),
                    'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener'
                );
            }
        }

        $this->parsePropertyCallbacks($container, $dispatcher);

        $this->parseCallback(
            $dispatcher,
            $this->getFromDca('list/presentation/breadcrumb_callback'),
            GetBreadcrumbEvent::NAME,
            array($container->getName()),
            'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener'
        );
    }

    /**
     * Parse the mode, flat, parented or hierarchical.
     *
     * @param BasicDefinitionInterface $config The basic definition of the data definition.
     *
     * @return void
     */
    protected function parseBasicMode(BasicDefinitionInterface $config)
    {
        if ($config->getMode() !== null) {
            return;
        }

        switch ($this->getFromDca('list/sorting/mode')) {
            case 0:
                // Records are not sorted.
            case 1:
                // Records are sorted by a fixed field.
            case 2:
                // Records are sorted by a switchable field.
            case 3:
                // Records are sorted by the parent table.
                $config->setMode(BasicDefinitionInterface::MODE_FLAT);
                break;
            case 4:
                // Displays the child records of a parent record (see style sheets module).
                $config->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
                break;
            case 5:
                // Records are displayed as tree (see site structure).
            case 6:
                // Displays the child records within a tree structure (see articles module).
                $config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
                break;
            default:
        }
    }

    /**
     * Parse the basic flags.
     *
     * @param BasicDefinitionInterface $config The basic definition of the data definition.
     *
     * @return void
     */
    protected function parseBasicFlags(BasicDefinitionInterface $config)
    {
        if (($switchToEdit = $this->getFromDca('config/switchToEdit')) !== null) {
            $config->setSwitchToEditEnabled((bool)$switchToEdit);
        }

        if (($value = $this->getFromDca('config/forceEdit')) !== null) {
            $config->setEditOnlyMode((bool)$value);
        }

        if (($value = $this->getFromDca('config/closed')) !== null) {
            $config
                ->setEditable(!$value)
                ->setCreatable(!$value);
        }

        if (($value = $this->getFromDca('config/notEditable')) !== null) {
            $config->setEditable(!$value);
        }

        if (($value = $this->getFromDca('config/notDeletable')) !== null) {
            $config->setDeletable(!$value);
        }

        if (($value = $this->getFromDca('config/notCreatable')) !== null) {
            $config->setCreatable(!(bool)$value);
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseBasicDefinition(ContainerInterface $container)
    {
        // Parse data provider.
        if ($container->hasBasicDefinition()) {
            $config = $container->getBasicDefinition();
        } else {
            $config = new DefaultBasicDefinition();
            $container->setBasicDefinition($config);
        }

        $this->parseBasicMode($config);
        $this->parseBasicFlags($config);

        if (($filters = $this->getFromDca('list/sorting/filter')) !== null) {
            if (is_array($filters) && !empty($filters)) {
                if ($config->hasAdditionalFilter()) {
                    $builder = FilterBuilder::fromArrayForRoot($config->getAdditionalFilter())->getFilter();
                } else {
                    $builder = FilterBuilder::fromArrayForRoot()->getFilter();
                }

                foreach ($filters as $filter) {
                    // FIXME: this only takes array('name', 'value') into account. Add support for: array('name=?', 'value').
                    $builder->andPropertyEquals($filter[0], $filter[1]);
                }

                $config->setAdditionalFilter($config->getDataProvider(), $builder->getAllAsArray());
            }
        }
    }

    /**
     * This method parses all data provider related information from Contao legacy data container arrays.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseDataProvider(ContainerInterface $container)
    {
        if ($container->hasDataProviderDefinition()) {
            $config = $container->getDataProviderDefinition();
        } else {
            $config = new DefaultDataProviderDefinition();
            $container->setDataProviderDefinition($config);
        }

        // If mode is 5, we need to define tree view.
        if ($this->getFromDca('list/sorting/mode') === 5) {
            if (!$container->getBasicDefinition()->getRootDataProvider()) {
                $container->getBasicDefinition()->setRootDataProvider($container->getName());
            }
        }

        if (($parentTable = $this->getFromDca('config/ptable')) !== null) {
            // Check config if it already exists, if not, add it.
            if (!$config->hasInformation($parentTable)) {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($parentTable);
                $config->addInformation($providerInformation);
            } else {
                $providerInformation = $config->getInformation($parentTable);
            }

            if ($providerInformation instanceof ContaoDataProviderInformation) {
                $initializationData = (array)$providerInformation->getInitializationData();

                $providerInformation
                    ->setTableName($parentTable)
                    ->setInitializationData(
                        array_merge(
                            array(
                                'source' => $parentTable,
                                'name'   => $parentTable,
                            ),
                            $initializationData
                        )
                    );

                if (!$container->getBasicDefinition()->getRootDataProvider()) {
                    $container->getBasicDefinition()->setRootDataProvider($parentTable);
                }
                if (!$container->getBasicDefinition()->getParentDataProvider()) {
                    $container->getBasicDefinition()->setParentDataProvider($parentTable);
                }
            }
        }

        $providerName = $container->getBasicDefinition()->getDataProvider() ?: $container->getName();

        // Check config if it already exists, if not, add it.
        if (!$config->hasInformation($providerName)) {
            $providerInformation = new ContaoDataProviderInformation();
            $providerInformation->setName($providerName);
            $config->addInformation($providerInformation);
        } else {
            $providerInformation = $config->getInformation($providerName);
        }

        if ($providerInformation instanceof ContaoDataProviderInformation) {
            $initializationData = (array)$providerInformation->getInitializationData();

            if (!isset($initializationData['source'])) {
                $providerInformation
                    ->setTableName($providerName)
                    ->setInitializationData(
                        array_merge(
                            array(
                                'source' => $providerName,
                                'name'   => $providerName,
                            ),
                            $initializationData
                        )
                    );
            }
            $providerInformation
                ->isVersioningEnabled((bool)$this->getFromDca('config/enableVersioning'));

            if (!$container->getBasicDefinition()->getDataProvider()) {
                $container->getBasicDefinition()->setDataProvider($providerName);
            }
        }
    }

    /**
     * This method parses the root entries definition.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseRootEntries(ContainerInterface $container)
    {
        if (is_array($root = $this->getFromDca('list/sorting/root'))) {
            $entries = $container->getBasicDefinition()->getRootEntries();

            $container->getBasicDefinition()->setRootEntries(array_merge($entries, $root));
        }
    }

    /**
     * Determine the root provider name from the container.
     *
     * @param ContainerInterface $container The container from where the name shall be retrieved.
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException If the root provider can not be determined.
     */
    protected function getRootProviderName(ContainerInterface $container)
    {
        $rootProvider = $container->getBasicDefinition()->getRootDataProvider();

        if (!$rootProvider) {
            throw new DcGeneralRuntimeException(
                'Root data provider name not specified in DCA but rootEntries section specified.'
            );
        }

        if (!$container->getDataProviderDefinition()->hasInformation($rootProvider)) {
            throw new DcGeneralRuntimeException('Unknown root data provider but rootEntries section specified.');
        }

        return $rootProvider;
    }

    /**
     * This method parses the parent-child conditions.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseParentChildConditions(ContainerInterface $container)
    {
        if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME)) {
            $definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        } else {
            $definition = new DefaultModelRelationshipDefinition();
        }

        // If mode is 5, we need to define tree view.
        if ($this->getFromDca('list/sorting/mode') === 5) {
            $rootProvider = $this->getRootProviderName($container);

            if (($relationship = $definition->getRootCondition()) === null) {
                $relationship = new RootCondition();
                $relationship
                    ->setSourceName($rootProvider);
                $definition->setRootCondition($relationship);

                $builder = FilterBuilder::fromArrayForRoot()->getFilter();
            } else {
                $builder = FilterBuilder::fromArrayForRoot($relationship->getFilterArray())->getFilter();
            }

            $relationship
                ->setSetters(
                    array_merge_recursive(
                        array(array('property' => 'pid', 'value' => '0'))
                    ),
                    $relationship->getSetters()
                );

            $builder->andPropertyEquals('pid', '0');

            $relationship
                ->setFilterArray($builder->getAllAsArray());

            if (($relationship = $definition->getChildCondition($rootProvider, $rootProvider)) === null) {
                $relationship = new ParentChildCondition();
                $relationship
                    ->setSourceName($rootProvider)
                    ->setDestinationName($rootProvider);
                $definition->addChildCondition($relationship);

                $builder = FilterBuilder::fromArray()->getFilter();
            } else {
                $builder = FilterBuilder::fromArray($relationship->getFilterArray())->getFilter();
            }

            $relationship
                ->setSetters(
                    array_merge_recursive(
                        array(array('to_field' => 'pid', 'from_field' => 'id'))
                    ),
                    $relationship->getSetters()
                );

            $builder->andRemotePropertyEquals('pid', 'id');
            $relationship
                ->setFilterArray($builder->getAllAsArray());

            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }

        // If ptable defined and no root setter we need to add (Contao default id=>pid mapping).
        if ($this->getFromDca('config/ptable') !== null) {
            $rootProvider = $this->getRootProviderName($container);

            if (($relationship = $definition->getRootCondition()) === null) {
                $relationship = new RootCondition();
                $relationship
                    ->setSourceName($rootProvider);

                $definition->setRootCondition($relationship);
            }

            if (!$relationship->getSetters()) {
                $relationship
                    ->setSetters(
                        array_merge_recursive(
                            array(array('property' => 'pid', 'value' => '0'))
                        ),
                        $relationship->getSetters()
                    );
            }

            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException If the stored backend view definition does not implement the correct
     *                                           interface.
     */
    protected function parseBackendView(ContainerInterface $container)
    {
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        } else {
            $view = new Contao2BackendViewDefinition();
            $container->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $view);
        }

        if (!$view instanceof Contao2BackendViewDefinitionInterface) {
            throw new DcGeneralInvalidArgumentException(
                'Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.'
            );
        }

        $this->parseListing($container, $view);
        $this->parsePropertySortingAndGroupings($view);
        $this->parsePanel($view);
        $this->parseGlobalOperations($view);
        $this->parseModelOperations($view);
    }

    /**
     * Parse the listing configuration.
     *
     * @param ContainerInterface                    $container The container where the data shall be stored.
     *
     * @param Contao2BackendViewDefinitionInterface $view      The view information for the backend view.
     *
     * @return void
     */
    protected function parseListing(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
    {
        $listing = $view->getListingConfig();
        $listDca = $this->getFromDca('list');

        if (($listing->getRootLabel() === null) && ($label = $this->getFromDca('config/label')) !== null) {
            $listing->setRootLabel($label);
        }

        if (($listing->getRootIcon() === null) && ($icon = $this->getFromDca('config/icon')) !== null) {
            $listing->setRootIcon($icon);
        }

        // Cancel if no list configuration found.
        if (!$listDca) {
            return;
        }

        $this->parseListSorting($listing, $listDca);
        $this->parseListLabel($container, $listing, $listDca);
    }

    /**
     * Parse the sorting and grouping information for all properties.
     *
     * @param Contao2BackendViewDefinitionInterface $view The view information for the backend view.
     *
     * @return void
     */
    protected function parsePropertySortingAndGroupings($view)
    {
        $definitions = $view->getListingConfig()->getGroupAndSortingDefinition();

        foreach ((array)$this->getFromDca('fields') as $propName => $propInfo) {
            $this->parsePropertySortingAndGrouping($propName, $propInfo, $definitions);
        }
    }

    /**
     * Parse the sorting and grouping information for a given property.
     *
     * @param string                                       $propName    The property to parse.
     *
     * @param array                                        $propInfo    The property information.
     *
     * @param GroupAndSortingDefinitionCollectionInterface $definitions The definitions.
     *
     * @return void
     */
    protected function parsePropertySortingAndGrouping($propName, $propInfo, $definitions)
    {
        if (empty($propInfo['sorting'])) {
            return;
        }

        $definition  = $definitions->add()->setName($propName);
        $information = $definition->add();
        $information->setProperty($propName);
        if (isset($propInfo['length'])) {
            $information->setGroupingLength($propInfo['length']);
        }

        $flag = empty($propInfo['flag']) ? $this->getFromDca('list/sorting/flag') : $propInfo['flag'];
        $this->evalFlag($information, $flag);
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * NOTE: this method currently does NOT support the custom SQL sorting information as supported by DC_Table in
     * Contao.
     *
     * @param ListingConfigInterface $listing The listing configuration definition to populate.
     *
     * @param array                  $listDca The DCA part containing the information to use.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException In case unsupported values are encountered.
     */
    protected function parseListSorting(ListingConfigInterface $listing, array $listDca)
    {
        $sortingDca = isset($listDca['sorting']) ? $listDca['sorting'] : array();

        if (isset($sortingDca['headerFields'])) {
            $listing->setHeaderPropertyNames((array)$sortingDca['headerFields']);
        }

        if (isset($sortingDca['icon'])) {
            $listing->setRootIcon($sortingDca['icon']);
        }

        if (isset($sortingDca['child_record_class'])) {
            $listing->setItemCssClass($sortingDca['child_record_class']);
        }

        if (empty($sortingDca['fields'])) {
            return;
        }

        $fieldsDca = $this->getFromDca('fields');

        $definitions = $listing->getGroupAndSortingDefinition();

        if (!$definitions->hasDefault()) {
            $definition = $definitions->add();
            $definitions->markDefault($definition);
        } else {
            $definition = $definitions->getDefault();
        }

        foreach ($sortingDca['fields'] as $field) {
            $groupAndSorting = $definition->add();

            if (isset($sortingDca['flag'])) {
                $this->evalFlag($groupAndSorting, $sortingDca['flag']);
            }

            if (preg_match('~^(\w+)(?: (.+))?$~', $field, $matches)) {
                $groupAndSorting
                    ->setProperty($matches[1])
                    ->setSortingMode(
                        (isset($matches[2])
                            ? $matches[2]
                            : GroupAndSortingInformationInterface::SORT_ASC)
                    );
            } else {
                throw new DcGeneralRuntimeException('Custom SQL in sorting fields are currently unsupported');
            }

            if (isset($fieldsDca[$groupAndSorting->getProperty()])) {
                if (isset($fieldsDca[$groupAndSorting->getProperty()]['flag'])) {
                    $flag = $fieldsDca[$groupAndSorting->getProperty()]['flag'];
                    $this->evalFlagGrouping($groupAndSorting, $flag);
                    $this->evalFlagGroupingLength($groupAndSorting, $flag);
                }
            }

            if (isset($sortingDca['disableGrouping']) && $sortingDca['disableGrouping']) {
                $groupAndSorting->setGroupingMode(GroupAndSortingInformationInterface::GROUP_NONE);
            }
        }
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param ContainerInterface     $container The container where the data shall be stored.
     *
     * @param ListingConfigInterface $listing   The listing configuration definition to populate.
     *
     * @param array                  $listDca   The DCA part containing the information to use.
     *
     * @return void
     */
    protected function parseListLabel(ContainerInterface $container, ListingConfigInterface $listing, array $listDca)
    {
        $labelDca   = isset($listDca['label']) ? $listDca['label'] : array();
        $formatter  = new DefaultModelFormatterConfig();
        $configured = false;

        if (isset($labelDca['fields'])) {
            $formatter->setPropertyNames($labelDca['fields']);
            $configured = true;
        }

        if (isset($labelDca['format'])) {
            $formatter->setFormat($labelDca['format']);
            $configured = true;
        }

        if (isset($labelDca['maxCharacters'])) {
            $formatter->setMaxLength($labelDca['maxCharacters']);
            $configured = true;
        }

        if ($configured) {
            $listing->setLabelFormatter($container->getBasicDefinition()->getDataProvider(), $formatter);
        }

        if (isset($labelDca['showColumns'])) {
            $listing->setShowColumns($labelDca['showColumns']);
        }
    }

    /**
     * Add filter elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelFilter(PanelRowInterface $row)
    {
        foreach ($this->getFromDca('fields') as $property => $value) {
            if (isset($value['filter'])) {
                $element = new DefaultFilterElementInformation();
                $element->setPropertyName($property);
                if (!$row->hasElement($element->getName())) {
                    $row->addElement($element);
                }
            }
        }
    }

    /**
     * Add sort element to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelSort(PanelRowInterface $row)
    {
        if ($row->hasElement('sort')) {
            $element = $row->getElement('sort');
        } else {
            $element = new DefaultSortElementInformation();
            $row->addElement($element);
        }

        foreach ($this->getFromDca('fields') as $property => $value) {
            if (isset($value['sorting'])) {
                $element->addProperty($property, (int)$value['flag']);
            }
        }

        $default = $this->getFromDca('list/sorting/flag');
        if ($default) {
            $element->setDefaultFlag($default);
        }
    }

    /**
     * Add search element to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelSearch(PanelRowInterface $row)
    {
        if ($row->hasElement('search')) {
            $element = $row->getElement('search');
        } else {
            $element = new DefaultSearchElementInformation();
        }
        foreach ($this->getFromDca('fields') as $property => $value) {
            if (isset($value['search'])) {
                $element->addProperty($property);
            }
        }
        if ($element->getPropertyNames() && !$row->hasElement('search')) {
            $row->addElement($element);
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelLimit(PanelRowInterface $row)
    {
        if (!$row->hasElement('limit')) {
            $row->addElement(new DefaultLimitElementInformation());
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelSubmit(PanelRowInterface $row)
    {
        if (!$row->hasElement('submit')) {
            $row->addElement(new DefaultSubmitElementInformation());
        }
    }

    /**
     * Parse a single panel row.
     *
     * @param PanelRowInterface $row         The row to be populated.
     *
     * @param string            $elementList A comma separated list of elements to be stored in the row.
     *
     * @return void
     */
    protected function parsePanelRow(PanelRowInterface $row, $elementList)
    {
        foreach (explode(',', $elementList) as $element) {
            switch ($element) {
                case 'filter':
                    $this->parsePanelFilter($row);
                    break;

                case 'sort':
                    $this->parsePanelSort($row);
                    break;

                case 'search':
                    $this->parsePanelSearch($row);
                    break;

                case 'limit':
                    $this->parsePanelLimit($row);
                    break;

                case 'submit':
                    $this->parsePanelSubmit($row);
                    break;

                default:
            }
        }
    }

    /**
     * Parse the defined palettes and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The listing configuration definition to populate.
     *
     * @return void
     */
    protected function parsePanel(Contao2BackendViewDefinitionInterface $view)
    {
        $layout = $view->getPanelLayout();
        $rows   = $layout->getRows();

        foreach (explode(';', (string)$this->getFromDca('list/sorting/panelLayout')) as $rowNo => $elementRow) {
            if ($rows->getRowCount() < ($rowNo + 1)) {
                $row = $rows->addRow();
            } else {
                $row = $rows->getRow($rowNo);
            }

            $this->parsePanelRow($row, $elementRow);

            if ($row->getCount() == 0) {
                $rows->deleteRow($rowNo);
            }
        }

        $hasSubmit = false;
        foreach ($rows as $row) {
            foreach ($row as $element) {
                if ($element instanceof SubmitElementInformationInterface) {
                    $hasSubmit = true;
                    break;
                }

                if ($hasSubmit) {
                    break;
                }
            }
        }

        if (!$hasSubmit && $rows->getRowCount()) {
            $row = $rows->getRow($rows->getRowCount() - 1);
            $row->addElement(new DefaultSubmitElementInformation(), 0);
        }
    }

    /**
     * Parse the defined container scoped operations and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view configuration definition to populate.
     *
     * @return void
     */
    protected function parseGlobalOperations(Contao2BackendViewDefinitionInterface $view)
    {
        $operationsDca = $this->getFromDca('list/global_operations');

        if (!is_array($operationsDca)) {
            return;
        }

        $collection = $view->getGlobalCommands();

        foreach (array_keys($operationsDca) as $operationName) {
            $command = $this->createCommand($operationName, $operationsDca[$operationName]);
            $collection->addCommand($command);
        }
    }

    /**
     * Parse the defined model scoped operations and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view configuration definition to populate.
     *
     * @return void
     */
    protected function parseModelOperations(Contao2BackendViewDefinitionInterface $view)
    {
        $operationsDca = $this->getFromDca('list/operations');

        if (!is_array($operationsDca)) {
            return;
        }

        $collection = $view->getModelCommands();

        foreach ($operationsDca as $operationName => $operationDca) {
            $command = $this->createCommand($operationName, $operationDca);
            $collection->addCommand($command);
        }
    }

    /**
     * Parse the defined palettes and populate the definition.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function parsePalettes(ContainerInterface $container)
    {
        $palettesDefinitionArray    = $this->getFromDca('palettes');
        $subPalettesDefinitionArray = $this->getFromDca('subpalettes');

        // Skip while there is no legacy palette definition.
        if (!is_array($palettesDefinitionArray)) {
            return;
        }

        // Ignore non-legacy sub palette definition.
        if (!is_array($subPalettesDefinitionArray)) {
            $subPalettesDefinitionArray = array();
        }

        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            $palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
        } else {
            $palettesDefinition = new DefaultPalettesDefinition();
            $container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
        }

        $palettesParser = new LegacyPalettesParser();
        $palettesParser->parse(
            $palettesDefinitionArray,
            $subPalettesDefinitionArray,
            $palettesDefinition
        );
    }

    /**
     * Parse the label of a single property.
     *
     * @param PropertyInterface $property The property to parse the label for.
     *
     * @param string|array      $label    The label value.
     *
     * @return void
     */
    protected function parseSinglePropertyLabel(PropertyInterface $property, $label)
    {
        if (!$property->getLabel()) {
            if (is_array($label)) {
                $lang        = $label;
                $label       = reset($lang);
                $description = next($lang);

                $property->setDescription($description);
            }

            $property->setLabel($label);
        }
    }

    /**
     * Parse a single property.
     *
     * @param PropertyInterface $property The property to parse.
     *
     * @param array             $propInfo The property information.
     *
     * @return void
     */
    protected function parseSingleProperty(PropertyInterface $property, array $propInfo)
    {
        foreach ($propInfo as $key => $value) {
            switch ($key) {
                case 'label':
                    $this->parseSinglePropertyLabel($property, $value);
                    break;

                case 'description':
                    if (!$property->getDescription()) {
                        $property->setDescription($value);
                    }
                    break;

                case 'default':
                    if (!$property->getDefaultValue()) {
                        $property->setDefaultValue($value);
                    }
                    break;

                case 'exclude':
                    $property->setExcluded((bool)$value);
                    break;

                case 'search':
                    $property->setSearchable((bool)$value);
                    break;

                case 'sorting':
                    $property->setSortable((bool)$value);
                    break;

                case 'filter':
                    $property->setFilterable((bool)$value);
                    break;

                case 'flag':
                    $this->evalFlag($property, $value);
                    break;

                case 'length':
                    $property->setGroupingLength($value);
                    break;

                case 'inputType':
                    $property->setWidgetType($value);
                    break;

                case 'options':
                    $property->setOptions($value);
                    break;

                case 'explanation':
                    $property->setExplanation($value);
                    break;

                case 'eval':
                    $property->setExtra(
                        array_merge(
                            (array)$property->getExtra(),
                            (array)$value
                        )
                    );
                    break;

                case 'reference':
                    $property->setExtra(
                        array_merge(
                            (array)$property->getExtra(),
                            array('reference' => &$propInfo['reference'])
                        )
                    );
                    break;

                default:
            }
        }
    }

    /**
     * Parse the defined properties and populate the definition.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseProperties(ContainerInterface $container)
    {
        if ($container->hasPropertiesDefinition()) {
            $definition = $container->getPropertiesDefinition();
        } else {
            $definition = new DefaultPropertiesDefinition();
            $container->setPropertiesDefinition($definition);
        }

        foreach ((array)$this->getFromDca('fields') as $propName => $propInfo) {
            if ($definition->hasProperty($propName)) {
                $property = $definition->getProperty($propName);
            } else {
                $property = new DefaultProperty($propName);
                $definition->addProperty($property);
            }

            $this->parseSingleProperty($property, $propInfo);
        }
    }

    /**
     * Create the correct command instance for the given information.
     *
     * @param string $commandName The name of the command to create.
     *
     * @param array  $commandDca  The Dca information of the command.
     *
     * @return CommandInterface|CutCommandInterface|CopyCommandInterface|ToggleCommandInterface
     */
    protected function createCommandInstance($commandName, array &$commandDca)
    {
        switch ($commandName) {
            case 'cut':
                return new CutCommand();

            case 'copy':
                return new CopyCommand();

            case 'toggle':
                $command = new ToggleCommand();

                if (isset($commandDca['toggleProperty'])) {
                    $command->setToggleProperty($commandDca['toggleProperty']);
                    unset($commandDca['toggleProperty']);
                } else {
                    // Implicit fallback to "published" as in Contao core.
                    $command->setToggleProperty('published');
                }

                if (isset($commandDca['toggleInverse'])) {
                    $command->setInverse($commandDca['toggleInverse']);
                    unset($commandDca['toggleInverse']);
                }

                return $command;
            default:
        }
        return new Command();
    }

    /**
     * Create a command from dca.
     *
     * @param string $commandName The name of the command to parse.
     *
     * @param array  $commandDca  The chunk from the DCA containing the command specification.
     *
     * @return CommandInterface
     */
    protected function createCommand($commandName, array $commandDca)
    {

        $command = $this->createCommandInstance($commandName, $commandDca);
        $command->setName($commandName);

        $parameters = $command->getParameters();

        if (isset($commandDca['href'])) {
            parse_str($commandDca['href'], $queryParameters);
            foreach ($queryParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            unset($commandDca['href']);
        }

        if (isset($commandDca['parameters'])) {
            foreach ($commandDca['parameters'] as $name => $value) {
                $parameters[$name] = $value;
            }
            unset($commandDca['parameters']);
        }

        if (isset($commandDca['label'])) {
            $lang = $commandDca['label'];

            if (is_array($lang)) {
                $label       = reset($lang);
                $description = next($lang);

                $command->setDescription($description);
            } else {
                $label = $lang;
            }

            $command->setLabel($label);

            unset($commandDca['label']);
        }

        if (isset($commandDca['description'])) {
            $command->setDescription($commandDca['description']);

            unset($commandDca['description']);
        }

        // Callback is transformed into event in parseCallbacks().
        unset($commandDca['button_callback']);

        if (count($commandDca)) {
            $extra = $command->getExtra();

            foreach ($commandDca as $name => $value) {
                $extra[$name] = $value;
            }
        }

        return $command;
    }

    /**
     * Evaluate the contao 2 sorting flag into sorting mode.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
     *
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlagSorting($config, $flag)
    {
        if (($flag < 0) || ($flag > 12)) {
            return;
        }

        if (($flag % 2) == 1) {
            $config->setSortingMode(GroupAndSortingInformationInterface::SORT_ASC);
        } else {
            $config->setSortingMode(GroupAndSortingInformationInterface::SORT_DESC);
        }
    }

    /**
     * Evaluate the contao 2 sorting flag into grouping mode.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
     *
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlagGrouping($config, $flag)
    {
        if (($flag < 0) || ($flag > 12)) {
            return;
        }

        if ($flag <= 4) {
            $config->setGroupingMode(GroupAndSortingInformationInterface::GROUP_CHAR);
        } elseif ($flag <= 6) {
            $config->setGroupingMode(GroupAndSortingInformationInterface::GROUP_DAY);
        } elseif ($flag <= 8) {
            $config->setGroupingMode(GroupAndSortingInformationInterface::GROUP_MONTH);
        } elseif ($flag <= 10) {
            $config->setGroupingMode(GroupAndSortingInformationInterface::GROUP_YEAR);
        } else {
            $config->setGroupingMode(GroupAndSortingInformationInterface::GROUP_NONE);
        }
    }

    /**
     * Evaluate the contao 2 sorting flag into grouping length.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
     *
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlagGroupingLength($config, $flag)
    {
        if (($flag == 1) || ($flag == 2)) {
            $config->setGroupingLength(1);
        } elseif (($flag == 3) || ($flag == 4)) {
            $config->setGroupingLength(2);
        }
    }

    /**
     * Evaluate the contao 2 sorting flag into sorting mode, grouping mode and grouping length.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
     *
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlag($config, $flag)
    {
        $this->evalFlagSorting($config, $flag);
        $this->evalFlagGrouping($config, $flag);
        $this->evalFlagGroupingLength($config, $flag);
    }
}
