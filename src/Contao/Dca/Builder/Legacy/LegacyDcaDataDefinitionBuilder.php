<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOptionsCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldGetWizardCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldGetXLabelCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnLoadCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnSaveCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\EmptyValueAwarePropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\BackCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CreateModelCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSubmitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SearchElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\SelectCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_merge_recursive;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_callable;
use function next;
use function parse_str;
use function reset;
use function trigger_error;

/**
 * Build the container config from legacy DCA syntax.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class LegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        $this->parseOderPropertyInPalette($container);
    }

    /**
     * Register the callback handlers for the given legacy callbacks.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param array                    $callbacks  The callbacks to be handled.
     * @param string                   $eventName  The event to be registered to.
     * @param array                    $arguments  The arguments to pass to the constructor.
     * @param class-string             $listener   The listener class to use.
     *
     * @return void
     *
     * @psalm-suppress DocblockTypeContradiction - only redundant when strict types active.
     * @psalm-suppress RedundantConditionGivenDocblockType - only redundant when strict types active.
     * @psalm-suppress RedundantCastGivenDocblockType - only redundant when strict types active.
     */
    protected function parseCallback($dispatcher, $callbacks, $eventName, $arguments, $listener)
    {
        if (!(is_array($callbacks) || is_callable($callbacks))) {
            return;
        }

        // If only one callback given, ensure the loop below handles it correctly.
        if (is_array($callbacks) && (2 === count($callbacks)) && !is_array($callbacks[0])) {
            $callbacks = [$callbacks];
        }
        foreach ((array) $callbacks as $callback) {
            if ($this->isCallbackBlacklisted($callback, $listener)) {
                continue;
            }

            $dispatcher->addListener(
                $eventName,
                new $listener($callback, $arguments)
            );
        }
    }

    /**
     * Check if callback is blacklisted.
     *
     * @param mixed  $callback The callback.
     * @param string $listener The listener class.
     *
     * @return bool
     */
    private function isCallbackBlacklisted($callback, $listener)
    {
        return ((ContainerOnLoadCallbackListener::class === $listener)
                && is_array($callback)
                && ('checkPermission' === $callback[1])
                && (0 === strpos($callback[0], 'tl_')));
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param ContainerInterface       $container  The container where the data shall be stored.
     * @param EventDispatcherInterface $dispatcher The event dispatcher in use.
     *
     * @return void
     */
    protected function parsePropertyCallbacks(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        foreach ((array) $this->getFromDca('fields') as $propName => $propInfo) {
            $args = [$container->getName(), $propName];
            foreach (
                [
                    'load_callback'        => [
                        'event' => DecodePropertyValueForWidgetEvent::NAME,
                        'class' => PropertyOnLoadCallbackListener::class
                    ],
                    'save_callback'        => [
                        'event' => EncodePropertyValueFromWidgetEvent::NAME,
                        'class' => PropertyOnSaveCallbackListener::class
                    ],
                    'options_callback'     => [
                        'event' => GetPropertyOptionsEvent::NAME,
                        'class' => ModelOptionsCallbackListener::class
                    ],
                    'input_field_callback' => [
                        'event' => BuildWidgetEvent::NAME,
                        'class' => PropertyInputFieldCallbackListener::class
                    ],
                    'wizard'               => [
                        'event' => ManipulateWidgetEvent::NAME,
                        'class' => PropertyInputFieldGetWizardCallbackListener::class
                    ],
                    'xlabel'               => [
                        'event' => ManipulateWidgetEvent::NAME,
                        'class' => PropertyInputFieldGetXLabelCallbackListener::class
                    ]
                ] as $name => $callback
            ) {
                if (isset($propInfo[$name])) {
                    $this->parseCallback($dispatcher, $propInfo[$name], $callback['event'], $args, $callback['class']);
                }
            }
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param ContainerInterface       $container  The container where the data shall be stored.
     * @param EventDispatcherInterface $dispatcher The event dispatcher in use.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function parseCallbacks(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        $args = [$container->getName()];
        foreach (
            [
                'config/onload_callback'                  => [
                    'event' => CreateDcGeneralEvent::NAME,
                    'class' => ContainerOnLoadCallbackListener::class
                ],
                'config/onsubmit_callback'                => [
                    'event' => PostPersistModelEvent::NAME,
                    'class' => ContainerOnSubmitCallbackListener::class
                ],
                'config/ondelete_callback'                => [
                    'event' => PostDeleteModelEvent::NAME,
                    'class' => ContainerOnDeleteCallbackListener::class
                ],
                'config/oncut_callback'                   => [
                    'event' => PostPasteModelEvent::NAME,
                    'class' => ContainerOnCutCallbackListener::class
                ],
                'config/oncopy_callback'                  => [
                    'event' => PostDuplicateModelEvent::NAME,
                    'class' => ContainerOnCopyCallbackListener::class
                ],
                'config/oninvalidate_cache_tags_callback' => [
                    'deprecated' => 'Dc-general not supported the config/oninvalidate_cache_tags_callback. ' .
                                    'Use the event ' . InvalidHttpCacheTagsEvent::class . ' for the data container ' .
                                    $container->getName() . '.'
                ],
                'list/sorting/header_callback'            => [
                    'event' => GetParentHeaderEvent::NAME,
                    'class' => ContainerHeaderCallbackListener::class
                ],
                'list/sorting/paste_button_callback'      => [
                    [
                        'event' => GetPasteRootButtonEvent::NAME,
                        'class' => ContainerPasteRootButtonCallbackListener::class
                    ],
                    [
                        'event' => GetPasteButtonEvent::NAME,
                        'class' => ContainerPasteButtonCallbackListener::class
                    ]
                ],
                'list/sorting/child_record_callback'      => [
                    'event' => ParentViewChildRecordEvent::NAME,
                    'class' => ModelChildRecordCallbackListener::class
                ],
                'list/label/group_callback'               => [
                    'event' => GetGroupHeaderEvent::NAME,
                    'class' => ModelGroupCallbackListener::class
                ],
                'list/label/label_callback'               => [
                    'event' => ModelToLabelEvent::NAME,
                    'class' => ModelLabelCallbackListener::class
                ],
                'list/presentation/breadcrumb_callback'   => [
                    'event' => GetBreadcrumbEvent::NAME,
                    'class' => ContainerGetBreadcrumbCallbackListener::class
                ]
            ] as $name => $callback
        ) {
            if ($callbacks = $this->getFromDca($name)) {
                if (isset($callback['event']) && isset($callback['class'])) {
                    $this->parseCallback($dispatcher, $callbacks, $callback['event'], $args, $callback['class']);

                    continue;
                }

                if (isset($callback['deprecated'])) {
                    // @codingStandardsIgnoreStart
                    @trigger_error($callback['deprecated']);
                    // @codingStandardsIgnoreEnd
                    continue;
                }

                /** @var list<array{event: string, class: class-string}> $callback */
                foreach ($callback as $sub) {
                    $this->parseCallback($dispatcher, $callbacks, $sub['event'], $args, $sub['class']);
                }
            }
        }

        foreach ((array) $this->getFromDca('list/global_operations') as $name => $operation) {
            if (isset($operation['button_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    [$operation['button_callback']],
                    GetGlobalButtonEvent::NAME,
                    [$container->getName(), $name],
                    ContainerGlobalButtonCallbackListener::class
                );
            }
        }

        foreach ((array) $this->getFromDca('list/operations') as $name => $operation) {
            if (isset($operation['button_callback'])) {
                $this->parseCallback(
                    $dispatcher,
                    [$operation['button_callback']],
                    GetOperationButtonEvent::NAME,
                    [$container->getName(), $name],
                    ModelOperationButtonCallbackListener::class
                );
            }
        }

        $this->parsePropertyCallbacks($container, $dispatcher);
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
        if (null !== $config->getMode()) {
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
        if (null !== ($switchToEdit = $this->getFromDca('config/switchToEdit'))) {
            $config->setSwitchToEditEnabled((bool) $switchToEdit);
        }

        if (null !== ($value = $this->getFromDca('config/forceEdit'))) {
            $config->setEditOnlyMode((bool) $value);
        }

        if (null !== ($value = $this->getFromDca('config/closed'))) {
            $config
                ->setEditable(!$value)
                ->setCreatable(!$value);
        }

        if (null !== ($value = $this->getFromDca('config/notEditable'))) {
            $config->setEditable(!$value);
        }

        if (null !== ($value = $this->getFromDca('config/notDeletable'))) {
            $config->setDeletable(!$value);
        }

        if (null !== ($value = $this->getFromDca('config/notCreatable'))) {
            $config->setCreatable(!(bool) $value);
        }

        if (null !== ($value = $this->getFromDca('config/dynamicPtable'))) {
            $config->setDynamicParentTable((bool) $value);
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

        if (
            (null !== ($filters = $this->getFromDca('list/sorting/filter')))
            && is_array($filters)
            && !empty($filters)
        ) {
            if ($config->hasAdditionalFilter()) {
                $builder = FilterBuilder::fromArrayForRoot($config->getAdditionalFilter() ?? [])->getFilter();
            } else {
                $builder = FilterBuilder::fromArrayForRoot()->getFilter();
            }

            foreach ($filters as $filter) {
                $builder->andPropertyEquals($filter[0], $filter[1]);
            }

            $config->setAdditionalFilter((string) $config->getDataProvider(), $builder->getAllAsArray());
        }
    }

    /**
     * This method parses all data provider related information from Contao legacy data container arrays.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if (
            (5 === $this->getFromDca('list/sorting/mode'))
            && !$container->getBasicDefinition()->getRootDataProvider()
        ) {
            $container->getBasicDefinition()->setRootDataProvider($container->getName());
        }

        if (null !== ($parentTable = $this->getFromDca('config/ptable'))) {
            // Check config if it already exists, if not, add it.
            if (!$config->hasInformation($parentTable)) {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($parentTable);
                $config->addInformation($providerInformation);
            } else {
                $providerInformation = $config->getInformation($parentTable);
            }

            if ($providerInformation instanceof ContaoDataProviderInformation) {
                $initializationData = (array) $providerInformation->getInitializationData();

                $providerInformation
                    ->setTableName($parentTable)
                    ->setInitializationData(
                        array_merge(
                            [
                                'source' => $parentTable,
                                'name'   => $parentTable,
                            ],
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
            $initializationData = (array) $providerInformation->getInitializationData();

            if (!isset($initializationData['source'])) {
                $providerInformation
                    ->setTableName($providerName)
                    ->setInitializationData(
                        array_merge(
                            [
                                'source' => $providerName,
                                'name'   => $providerName
                            ],
                            $initializationData
                        )
                    );
            }
            $providerInformation->setVersioningEnabled(false);
            if (true === (bool) $this->getFromDca('config/enableVersioning')) {
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Versioning is not supported yet and will get implemented in a future release.',
                    E_USER_WARNING
                );
                // @codingStandardsIgnoreEnd
            }

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
            $entries = $container->getBasicDefinition()->getRootEntries() ?? [];

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
        $definition = $container->hasDefinition(ModelRelationshipDefinitionInterface::NAME)
            ? $container->getDefinition(ModelRelationshipDefinitionInterface::NAME)
            : new DefaultModelRelationshipDefinition();
        assert($definition instanceof ModelRelationshipDefinitionInterface);

        // If mode is 5, we need to define tree view.
        if (5 === $this->getFromDca('list/sorting/mode')) {
            $rootProvider = $this->getRootProviderName($container);

            if (null === ($relationship = $definition->getRootCondition())) {
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
                        [['property' => 'pid', 'value' => '0']],
                        $relationship->getSetters()
                    ),
                );

            $builder->andPropertyEquals('pid', '0');

            $relationship
                ->setFilterArray($builder->getAllAsArray());

            if (null === ($relationship = $definition->getChildCondition($rootProvider, $rootProvider))) {
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
                        [['to_field' => 'pid', 'from_field' => 'id']],
                        $relationship->getSetters()
                    ),
                );

            $builder->andRemotePropertyEquals('pid', 'id');
            $relationship
                ->setFilterArray($builder->getAllAsArray());

            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }

        // If ptable defined and no root setter we need to add (Contao default id=>pid mapping).
        if (null !== $this->getFromDca('config/ptable')) {
            $rootProvider = $this->getRootProviderName($container);

            if (null === ($relationship = $definition->getRootCondition())) {
                $relationship = new RootCondition();
                $relationship
                    ->setSourceName($rootProvider);

                $definition->setRootCondition($relationship);
            }

            if (!$relationship->getSetters()) {
                $relationship
                    ->setSetters(
                        array_merge_recursive(
                            [['property' => 'pid', 'value' => '0']],
                            $relationship->getSetters()
                        ),
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

        $parsedProperties = $this->parseListing($container, $view);
        $this->parsePropertySortingAndGroupings($view, $parsedProperties);
        $this->parsePanel($view);
        $this->parseGlobalOperations($view);
        $this->parseModelOperations($view);
    }

    /**
     * Parse the listing configuration.
     *
     * @param ContainerInterface                    $container The container where the data shall be stored.
     * @param Contao2BackendViewDefinitionInterface $view      The view information for the backend view.
     *
     * @return array
     */
    protected function parseListing(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
    {
        $listing = $view->getListingConfig();
        $listDca = $this->getFromDca('list');

        if ((null === $listing->getRootLabel()) && (null !== ($label = $this->getFromDca('config/label')))) {
            $listing->setRootLabel($label);
        }

        if ((null === $listing->getRootIcon()) && (null !== ($icon = $this->getFromDca('config/icon')))) {
            $listing->setRootIcon($icon);
        }

        // Cancel if no list configuration found.
        if (!$listDca) {
            return [];
        }

        $parsedProperties = $this->parseListSorting($listing, $listDca);
        $this->parseListLabel($container, $listing, $listDca);

        return $parsedProperties;
    }

    /**
     * Parse the sorting and grouping information for all properties.
     *
     * @param Contao2BackendViewDefinitionInterface $view             The view information for the backend view.
     * @param array                                 $parsedProperties A list of properties already parsed.
     *
     * @return void
     */
    protected function parsePropertySortingAndGroupings($view, $parsedProperties)
    {
        $definitions = $view->getListingConfig()->getGroupAndSortingDefinition();

        foreach ((array) $this->getFromDca('fields') as $propName => $propInfo) {
            $this->parsePropertySortingAndGrouping($propName, $propInfo, $definitions, $parsedProperties);
        }
    }

    /**
     * Parse the sorting and grouping information for a given property.
     *
     * @param string                                       $propName         The property to parse.
     * @param array                                        $propInfo         The property information.
     * @param GroupAndSortingDefinitionCollectionInterface $definitions      The definitions.
     * @param array                                        $parsedProperties A list of properties already parsed.
     *
     * @return void
     */
    protected function parsePropertySortingAndGrouping($propName, $propInfo, $definitions, $parsedProperties)
    {
        if (empty($propInfo['sorting']) || in_array($propName, $parsedProperties)) {
            return;
        }

        $definition  = $definitions->add()->setName($propName);
        $information = $definition->add();
        $information->setProperty($propName);
        if (isset($propInfo['length'])) {
            $information->setGroupingLength($propInfo['length']);
        }

        // Special case for field named "sorting" in Contao.
        if ('sorting' === $propName) {
            $information->setManualSorting();
        }

        // If no default sorting and grouping definition is defined, assume the first one is default.
        if (!$definitions->hasDefault()) {
            $definitions->markDefault($definition);
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
     * @param array                  $listDca The DCA part containing the information to use.
     *
     * @return array
     *
     * @throws DcGeneralRuntimeException In case unsupported values are encountered.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function parseListSorting(ListingConfigInterface $listing, array $listDca)
    {
        $parsedProperties = [];
        $sortingDca       = ($listDca['sorting'] ?? []);

        if (isset($sortingDca['headerFields'])) {
            $listing->setHeaderPropertyNames((array) $sortingDca['headerFields']);
        }

        if (isset($sortingDca['icon'])) {
            $listing->setRootIcon($sortingDca['icon']);
        }

        if (isset($sortingDca['child_record_class'])) {
            $listing->setItemCssClass($sortingDca['child_record_class']);
        }

        if (empty($sortingDca['fields'])) {
            return $parsedProperties;
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
                        ($matches[2] ?? GroupAndSortingInformationInterface::SORT_ASC)
                    );

                // Special case for field named "sorting" in Contao.
                if ('sorting' === $field) {
                    $groupAndSorting->setManualSorting();
                }
            } else {
                throw new DcGeneralRuntimeException('Custom SQL in sorting fields are currently unsupported');
            }

            if (isset($fieldsDca[$groupAndSorting->getProperty()])) {
                if (isset($fieldsDca[$groupAndSorting->getProperty()]['flag'])) {
                    $flag = $fieldsDca[$groupAndSorting->getProperty()]['flag'];
                    $this->evalFlagGrouping($groupAndSorting, $flag);
                    $this->evalFlagGroupingLength($groupAndSorting, $flag);
                }

                if (1 === count($sortingDca['fields'])) {
                    $definition->setName($groupAndSorting->getProperty());
                    $parsedProperties[] = $groupAndSorting->getProperty();
                }
            }

            if (isset($sortingDca['disableGrouping']) && $sortingDca['disableGrouping']) {
                $groupAndSorting->setGroupingMode(GroupAndSortingInformationInterface::GROUP_NONE);
            }
        }

        return $parsedProperties;
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param ContainerInterface     $container The container where the data shall be stored.
     * @param ListingConfigInterface $listing   The listing configuration definition to populate.
     * @param array                  $listDca   The DCA part containing the information to use.
     *
     * @return void
     */
    protected function parseListLabel(ContainerInterface $container, ListingConfigInterface $listing, array $listDca)
    {
        $labelDca   = ($listDca['label'] ?? []);
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
            $listing->setLabelFormatter((string) $container->getBasicDefinition()->getDataProvider(), $formatter);
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
        if (!$row->hasElement('sort')) {
            $row->addElement(new DefaultSortElementInformation());
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
        assert($element instanceof SearchElementInformationInterface);
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

        foreach (explode(';', (string) $this->getFromDca('list/sorting/panelLayout')) as $rowNo => $elementRow) {
            if ($rows->getRowCount() < ($rowNo + 1)) {
                $row = $rows->addRow();
            } else {
                $row = $rows->getRow($rowNo);
            }

            $this->parsePanelRow($row, $elementRow);

            if (0 === $row->getCount()) {
                $rows->deleteRow($rowNo);
            }
        }

        if (!$this->hasSubmit($rows) && $rows->getRowCount()) {
            $row = $rows->getRow($rows->getRowCount() - 1);
            $row->addElement(new DefaultSubmitElementInformation(), 0);
        }
    }

    /**
     * Check if the rows is somewhere a submit element.
     *
     * @param PanelRowCollectionInterface $rows The panel rows.
     *
     * @return bool
     */
    private function hasSubmit($rows)
    {
        foreach ($rows as $row) {
            foreach ($row as $element) {
                if ($element instanceof SubmitElementInformationInterface) {
                    return true;
                }
            }
        }

        return false;
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

        $collection = $view->getGlobalCommands();
        $collection->addCommand(new BackCommand());
        $collection->addCommand(new CreateModelCommand());

        if (!is_array($operationsDca)) {
            return;
        }

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
            $subPalettesDefinitionArray = [];
        }

        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            $palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
        } else {
            $palettesDefinition = new DefaultPalettesDefinition();
            $container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
        }
        assert($palettesDefinition instanceof PalettesDefinitionInterface);

        $palettesParser = new LegacyPalettesParser();
        $palettesParser->parse(
            $palettesDefinitionArray,
            $subPalettesDefinitionArray,
            $palettesDefinition
        );
    }

    /**
     * Parse if the order property is defined in the same palette as the corresponding source property.
     * If not, then define it.
     *
     * @param ContainerInterface $container The container.
     *
     * @return void
     */
    private function parseOderPropertyInPalette(ContainerInterface $container)
    {
        foreach ($container->getPropertiesDefinition()->getProperties() as $property) {
            $extra = $property->getExtra();
            if (
                !isset($extra['orderField'])
                || !$container->getPropertiesDefinition()->hasProperty($extra['orderField'])
            ) {
                continue;
            }

            $orderProperty = $container->getPropertiesDefinition()->getProperty($extra['orderField']);
            if (false === (bool) $orderProperty->getWidgetType()) {
                continue;
            }

            foreach ($container->getPalettesDefinition()->getPalettes() as $palette) {
                foreach ($palette->getLegends() as $legend) {
                    if (
                        (false === $legend->hasProperty($property->getName()))
                        || (true === $legend->hasProperty($orderProperty->getName()))
                    ) {
                        continue;
                    }

                    $paletteProperty      = $legend->getProperty($property->getName());
                    $paletteOrderProperty = new Property($orderProperty->getName());
                    $legend->addProperty($paletteOrderProperty, $paletteProperty);

                    $paletteOrderProperty->setEditableCondition($paletteProperty->getEditableCondition());
                    $visibleCondition = new PropertyTrueCondition($property->getName(), false);
                    $paletteOrderProperty->setVisibleCondition($visibleCondition);
                }
            }
        }
    }

    /**
     * Parse the label of a single property.
     *
     * @param PropertyInterface $property The property to parse the label for.
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
     * @param array             $propInfo The property information.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                    $property->setExcluded((bool) $value);
                    break;

                case 'search':
                    $property->setSearchable((bool) $value);
                    break;

                case 'filter':
                    $property->setFilterable((bool) $value);
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
                            $property->getExtra(),
                            (array) $value
                        )
                    );
                    break;

                case 'reference':
                    $property->setExtra(
                        array_merge(
                            $property->getExtra(),
                            ['reference' => &$propInfo['reference']]
                        )
                    );
                    break;

                case 'sql':
                    $this->determineEmptyValueFromSql($property, $value);
                    break;

                default:
            }
        }

        $this->parseWidgetPageTree($property, $propInfo);
    }

    /**
     * Parse the property widget type of page tree.
     *
     * @param PropertyInterface $property The property to parse.
     * @param array             $propInfo The property information.
     *
     * @return void
     */
    private function parseWidgetPageTree(PropertyInterface $property, array $propInfo)
    {
        if (isset($propInfo['sourceName']) || ('pageTree' !== $property->getWidgetType())) {
            return;
        }

        // If the foreign key not set, then use an standard as fallback.
        if (!isset($propInfo['foreignKey'])) {
            $propInfo['foreignKey'] = 'tl_page.title';
        }

        $property
            ->setExtra(
                array_merge(
                    [
                        'sourceName' => explode('.', $propInfo['foreignKey'])[0],
                        'idProperty' => 'id'
                    ],
                    $property->getExtra()
                )
            );
    }

    /**
     * Parse the property for order and set the order widget.
     *
     * @param PropertyInterface $property      The base property.
     * @param PropertyInterface $orderProperty The order property.
     *
     * @return void
     */
    private function parseOrderProperty(PropertyInterface $property, PropertyInterface $orderProperty)
    {
        $orderWidgets = [
            'pageTree'            => 'pageTreeOrder',
            'fileTree'            => 'fileTreeOrder',
            'DcGeneralTreePicker' => 'treePickerOrder'
        ];
        if (false === array_key_exists($property->getWidgetType(), $orderWidgets)) {
            return;
        }

        $orderProperty->setWidgetType($orderWidgets[$property->getWidgetType()]);
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

        foreach ((array) $this->getFromDca('fields') as $propName => $propInfo) {
            if ($definition->hasProperty($propName)) {
                $property = $definition->getProperty($propName);
            } else {
                $property = new DefaultProperty($propName);
                $definition->addProperty($property);
            }

            // Some extensions create invalid DCA information and the DCA must therefore be validated.
            if (!is_array($propInfo)) {
                continue;
            }

            $this->parseSingleProperty($property, $propInfo);

            $extra = $property->getExtra();
            if (
                isset($extra['orderField'])
                && array_key_exists($extra['orderField'], (array) $this->getFromDca('fields'))
            ) {
                if (!$definition->hasProperty($extra['orderField'])) {
                    $definition->addProperty(new DefaultProperty($extra['orderField']));
                }

                $orderProperty = $definition->getProperty($extra['orderField']);
                $this->parseOrderProperty($property, $orderProperty);
            }
        }
    }

    /**
     * Create the correct command instance for the given information.
     *
     * @param string $commandName The name of the command to create.
     * @param array  $commandDca  The Dca information of the command.
     *
     * @return CommandInterface
     */
    protected function createCommandInstance($commandName, array &$commandDca)
    {
        switch ($commandName) {
            case 'cut':
                return new CutCommand();

            case 'copy':
            case 'deepcopy':
                return new CopyCommand();

            case 'all':
                return new SelectCommand();

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
     * @param array  $commandDca  The chunk from the DCA containing the command specification.
     *
     * @return CommandInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlagSorting($config, $flag)
    {
        if (($flag < 0) || ($flag > 12)) {
            return;
        }

        if (1 === ($flag % 2)) {
            $config->setSortingMode(GroupAndSortingInformationInterface::SORT_ASC);
        } else {
            $config->setSortingMode(GroupAndSortingInformationInterface::SORT_DESC);
        }
    }

    /**
     * Evaluate the contao 2 sorting flag into grouping mode.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
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
     * @param int                                 $flag   The flag to be evaluated.
     *
     * @return void
     */
    protected function evalFlagGroupingLength($config, $flag)
    {
        if ((1 === $flag) || (2 === $flag)) {
            $config->setGroupingLength(1);
        } elseif ((3 === $flag) || (4 === $flag)) {
            $config->setGroupingLength(2);
        }
    }

    /**
     * Evaluate the contao 2 sorting flag into sorting mode, grouping mode and grouping length.
     *
     * @param GroupAndSortingInformationInterface $config The property to evaluate the flag for.
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

    /**
     * Try to determine the empty type from SQL type.
     *
     * @param PropertyInterface $property The property to store the value into.
     * @param string            $sqlType  The SQL type.
     *
     * @return void
     */
    private function determineEmptyValueFromSql(PropertyInterface $property, $sqlType)
    {
        if ($property instanceof EmptyValueAwarePropertyInterface) {
            $property->setEmptyValue(Widget::getEmptyValueByFieldType($sqlType));
        }
    }
}
