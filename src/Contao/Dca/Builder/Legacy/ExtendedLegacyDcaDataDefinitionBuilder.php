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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Definition\ExtendedDca;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Build the container config from legacy DCA syntax.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ExtendedLegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
    {
        if (!$this->loadDca($container->getName(), $this->getDispatcher())) {
            return;
        }

        $this->parseBasicDefinition($container);
        $this->parseDataProvider($container);
        $this->parsePalettes($container);
        $this->parseConditions($container);
        $this->parseBackendView($container);
        $this->parseClassNames($container);
        $this->loadAdditionalDefinitions($container);
        $this->parseDynamicParentTableProperty($container);
    }

    /**
     * Ensure that the basic configuration is set in the definition.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parseBasicDefinition(ContainerInterface $container)
    {
        if (!$container->hasBasicDefinition()) {
            $container->setBasicDefinition(new DefaultBasicDefinition());
        }
    }

    /**
     * Load all additional definitions, like naming of parent data provider etc.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function loadAdditionalDefinitions(ContainerInterface $container)
    {
        if (null !== $this->getFromDca('dca_config/data_provider')) {
            $dataContainerName = $container->getName();
            $this->getDispatcher()->addListener(
                PopulateEnvironmentEvent::NAME,
                function (PopulateEnvironmentEvent $event) use ($dataContainerName) {
                    $environment = $event->getEnvironment();
                    $definition  = $environment->getDataDefinition();
                    $dispatcher  = $environment->getEventDispatcher();
                    $translator  = $environment->getTranslator();
                    assert($definition instanceof ContainerInterface);
                    assert($dispatcher instanceof EventDispatcherInterface);
                    assert($translator instanceof TranslatorInterface);
                    if ($definition->getName() !== $dataContainerName) {
                        return;
                    }

                    $parentName = $definition->getBasicDefinition()->getParentDataProvider();
                    if ($parentName) {
                        $parentDefinition = ($parentName === $definition->getName())
                            ? $definition
                            : (new DcGeneralFactory())
                                ->setEventDispatcher($dispatcher)
                                ->setTranslator($translator)
                                ->setContainerName($parentName)
                                ->createDcGeneral()
                                ->getEnvironment()
                                ->getDataDefinition();
                        assert($parentDefinition instanceof ContainerInterface);
                        $environment->setParentDataDefinition($parentDefinition);
                    }

                    $rootName = $definition->getBasicDefinition()->getRootDataProvider();
                    if ($rootName) {
                        $rootDefinition = ($rootName === $definition->getName())
                            ? $definition
                            : (new DcGeneralFactory())
                                ->setEventDispatcher($dispatcher)
                                ->setTranslator($translator)
                                ->setContainerName($rootName)
                                ->createDcGeneral()
                                ->getEnvironment()
                                ->getDataDefinition();
                        assert($rootDefinition instanceof ContainerInterface);
                        $environment->setRootDataDefinition($rootDefinition);
                    }
                }
            );
        }
    }

    /**
     * Parse all class names for view, controller and callback class.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When the container is of invalid type.
     */
    protected function parseClassNames(ContainerInterface $container)
    {
        if ($container->hasDefinition(ExtendedDca::NAME)) {
            $definition = $container->getDefinition(ExtendedDca::NAME);

            if (!($definition instanceof ExtendedDca)) {
                throw new DcGeneralInvalidArgumentException(
                    \sprintf(
                        'Definition with name %s must be an instance of ExtendedDca but instance of %s encountered.',
                        ExtendedDca::NAME,
                        \get_class($definition)
                    )
                );
            }
        } else {
            $definition = new ExtendedDca();
            $container->setDefinition(ExtendedDca::NAME, $definition);
        }

        if (null === $this->getFromDca('dca_config')) {
            return;
        }

        if (null !== ($class = $this->getFromDca('dca_config/controller'))) {
            $definition->setControllerClass($class);
        }

        if (null !== ($class = $this->getFromDca('dca_config/view'))) {
            $definition->setViewClass($class);
        }
    }

    /**
     * Test if a data provider name is a special name.
     *
     * @param string $name The name to test.
     *
     * @return bool
     */
    protected function isSpecialName($name)
    {
        return \in_array($name, ['default', 'root', 'parent']);
    }

    /**
     * Parse a single data provider information and prepare the definition object for it.
     *
     * @param ContainerInterface              $container   The container where the data shall be stored.
     * @param DataProviderDefinitionInterface $providers   The data provider container.
     * @param array                           $information The information for the data provider to be parsed.
     * @param string|null                     $name        The name of the data provider to be used within the
     *                                                     container.
     *
     * @return DataProviderInformationInterface|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function parseSingleDataProvider(
        ContainerInterface $container,
        DataProviderDefinitionInterface $providers,
        array $information,
        $name
    ) {
        if (isset($information['factory'])) {
            $providerInformation = (new \ReflectionClass($information['factory']))->newInstance()->build($information);
        } else {
            // Determine the name.
            if ($name && !$this->isSpecialName($name)) {
                $providerName = $name;
            } elseif ('default' === $name) {
                $providerName = $container->getName();
            } elseif (isset($information['source'])) {
                $providerName = $information['source'];
            } else {
                $providerName = $container->getName();
            }

            // Check config if it already exists, if not, add it.
            if (!$providers->hasInformation($providerName)) {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($providerName);
                $providers->addInformation($providerInformation);
            } else {
                $providerInformation = $providers->getInformation($providerName);
            }

            if (!$providerInformation instanceof ContaoDataProviderInformation) {
                return $providerInformation;
            }
            if (!$providerInformation->getTableName()) {
                if (isset($information['source'])) {
                    $providerInformation
                        ->setTableName($information['source']);
                } else {
                    $providerInformation
                        ->setTableName($providerName);
                }
            }

            if (isset($information['class'])) {
                $providerInformation->setClassName($information['class']);
            }
        }

        return $providerInformation;
    }

    /**
     * This method parses all data provider related information from Contao legacy data container arrays.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function parseDataProvider(ContainerInterface $container)
    {
        // Parse data provider.
        if ($container->hasDataProviderDefinition()) {
            $config = $container->getDataProviderDefinition();
        } else {
            $config = new DefaultDataProviderDefinition();
            $container->setDataProviderDefinition($config);
        }

        // First check if we are using the "new" notation used in DcGeneral 0.9.
        if (!\is_array($this->getFromDca('dca_config/data_provider'))) {
            return;
        }

        $dataProvidersDca = $this->getFromDca('dca_config/data_provider');

        foreach ($dataProvidersDca as $dataProviderDcaName => $dataProviderDca) {
            $providerInformation = $this->parseSingleDataProvider(
                $container,
                $config,
                $dataProviderDca,
                $dataProviderDcaName
            );

            if ($providerInformation instanceof ContaoDataProviderInformation) {
                $initializationData     = (array) $providerInformation->getInitializationData();
                $baseInitializationData = [
                    'name' => $dataProviderDcaName
                ];

                switch ((string) $dataProviderDcaName) {
                    case 'default':
                        $providerInformation->setVersioningEnabled(
                            (bool) $this->getFromDca('config/enableVersioning')
                        );

                        $container->getBasicDefinition()->setDataProvider($providerInformation->getName());
                        $baseInitializationData['name'] = $providerInformation->getName();
                        break;

                    case 'root':
                        $container->getBasicDefinition()->setRootDataProvider($providerInformation->getName());
                        $baseInitializationData['name'] = $providerInformation->getName();
                        break;

                    case 'parent':
                        $container->getBasicDefinition()->setParentDataProvider($providerInformation->getName());
                        $baseInitializationData['name'] = $providerInformation->getName();
                        break;

                    default:
                }

                $providerInformation->setInitializationData(
                    \array_merge(
                        $baseInitializationData,
                        $dataProviderDca,
                        $initializationData
                    )
                );
            }
        }
    }

    /**
     * Parse the palette information.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     */
    protected function parsePalettes(ContainerInterface $container)
    {
        $palettesDca = $this->getFromDca('palettes');

        // Skip while there is no extended palette definition.
        if (!\is_callable($palettesDca)) {
            return;
        }

        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            $palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
        } else {
            $palettesDefinition = new DefaultPalettesDefinition();
            $container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
        }

        $palettesDca($palettesDefinition, $container);
    }

    /**
     * Parse the root condition.
     *
     * @param ContainerInterface                   $container  The container where the data shall be stored.
     * @param ModelRelationshipDefinitionInterface $definition The relationship definition.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If no root data provider is defined.
     */
    protected function parseRootCondition(
        ContainerInterface $container,
        ModelRelationshipDefinitionInterface $definition
    ) {
        if (null !== ($rootCondition = $this->getFromDca('dca_config/rootEntries'))) {
            $rootProvider = $container->getBasicDefinition()->getRootDataProvider();

            if (!$rootProvider) {
                throw new DcGeneralRuntimeException(
                    'Root data provider name not specified in DCA but rootEntries section specified.'
                );
            }

            if (!$container->getDataProviderDefinition()->hasInformation($rootProvider)) {
                throw new DcGeneralRuntimeException('Unknown root data provider but rootEntries section specified.');
            }

            if (isset($rootCondition[$rootProvider])) {
                $rootCondition = $rootCondition[$rootProvider];
                $mySetter      = $rootCondition['setOn'];

                if (null === ($relationship = $definition->getRootCondition())) {
                    $relationship = new RootCondition();
                    $setter       = $mySetter;
                    $builder      = FilterBuilder::fromArrayForRoot()->getFilter();
                } else {
                    /** @var RootConditionInterface $relationship */
                    if ($relationship->getSetters()) {
                        $setter = \array_merge_recursive($mySetter, $relationship->getSetters());
                    } else {
                        $setter = $mySetter;
                    }
                    $builder = FilterBuilder::fromArrayForRoot($relationship->getFilterArray())->getFilter();
                }

                $builder->append(FilterBuilder::fromArrayForRoot((array) $rootCondition['filter']));

                $relationship
                    ->setSourceName($rootProvider)
                    ->setFilterArray($builder->getAllAsArray())
                    ->setSetters($setter);
                $definition->setRootCondition($relationship);
            }
        }
    }

    /**
     * Parse the root condition.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship definition.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If no root data provider is defined.
     */
    protected function parseParentChildConditions(
        ModelRelationshipDefinitionInterface $definition
    ) {
        if (null !== ($childConditions = $this->getFromDca('dca_config/childCondition'))) {
            foreach ((array) $childConditions as $childCondition) {
                /** @var ParentChildConditionInterface $relationship */
                $relationship = $definition->getChildCondition($childCondition['from'], $childCondition['to']);
                if (!$relationship instanceof ParentChildConditionInterface) {
                    $relationship = new ParentChildCondition();
                    $relationship
                        ->setSourceName($childCondition['from'])
                        ->setDestinationName($childCondition['to']);
                    $definition->addChildCondition($relationship);
                    $setter  = $childCondition['setOn'];
                    $inverse = $childCondition['inverse'] ?? [];
                } else {
                    $setter  = \array_merge_recursive((array) $childCondition['setOn'], $relationship->getSetters());
                    $inverse = \array_merge_recursive(
                        $childCondition['inverse'] ?? [],
                        $relationship->getInverseFilterArray()
                    );
                }

                $relationship
                    ->setFilterArray(
                        FilterBuilder::fromArray($relationship->getFilterArray())
                            ->getFilter()
                            ->append(
                                FilterBuilder::fromArray((array) $childCondition['filter'])
                            )
                            ->getAllAsArray()
                    )
                    ->setSetters($setter)
                    ->setInverseFilterArray($inverse);
            }
        }
    }

    /**
     * Parse the conditions for model relationships from the definition.
     *
     * This includes root entry filters, parent child relationship.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If any information is missing or invalid.
     */
    protected function parseConditions(ContainerInterface $container)
    {
        if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME)) {
            $definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        } else {
            $definition = new DefaultModelRelationshipDefinition();
            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }
        if (!$definition instanceof ModelRelationshipDefinitionInterface) {
            throw new DcGeneralInvalidArgumentException(
                'Configured ModelRelationshipDefinition does not implement ModelRelationshipDefinitionInterface.'
            );
        }

        $this->parseRootCondition($container, $definition);
        $this->parseParentChildConditions($definition);
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * This method expects to find an instance of Contao2BackendViewDefinitionInterface in the container.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException If the stored definition in the container is of invalid type.
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

        $this->parseListing($view);
    }

    /**
     * Parse the listing configuration.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view definition.
     *
     * @return void
     */
    protected function parseListing(Contao2BackendViewDefinitionInterface $view)
    {
        $this->parseListLabel($view->getListingConfig());
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param ListingConfigInterface $listing The listing configuration to be populated.
     *
     * @return void
     */
    protected function parseListLabel(ListingConfigInterface $listing)
    {
        if (null === ($formats = $this->getFromDca('dca_config/child_list'))) {
            return;
        }

        foreach ($formats as $providerName => $format) {
            $formatter  = new DefaultModelFormatterConfig();
            $configured = false;

            if (isset($format['fields'])) {
                $formatter->setPropertyNames($format['fields']);
                $configured = true;
            }

            if (isset($format['format'])) {
                $formatter->setFormat($format['format']);
                $configured = true;
            }

            if (isset($format['maxCharacters'])) {
                $formatter->setMaxLength($format['maxCharacters']);
                $configured = true;
            }

            if ($configured) {
                $listing->setLabelFormatter($providerName, $formatter);
            }
        }
    }

    /**
     * Parse the dynamic parent table.
     *
     * @param ContainerInterface $container The container where the data shall be stored.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     */
    protected function parseDynamicParentTableProperty(ContainerInterface $container)
    {
        if (
            (null === ($propertyName = $this->getFromDca('dca_config/parent_table_property')))
            || (null === ($sourceProvider = $this->getFromDca('config/ptable')))
            || (null === ($dynamicParentTable = $this->getFromDca('config/dynamicPtable')))
        ) {
            return;
        }

        $relationship   = $container->getModelRelationshipDefinition();
        $childCondition = $relationship->getChildCondition($sourceProvider, $container->getName());
        if (null === $childCondition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }

        $childCondition->setFilterArray(
            \array_merge(
                $childCondition->getFilterArray(),
                [
                    [
                        'local'        => $propertyName,
                        'remote_value' => $sourceProvider,
                        'operation'    => '='
                    ]
                ]
            )
        );

        $backendView = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);
        $backendView->getListingConfig()->setParentTablePropertyName($propertyName);
        $container->getBasicDefinition()->setDynamicParentTable($dynamicParentTable);
    }
}
