<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Definition\ExtendedDca;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
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

/**
 * Build the container config from legacy DCA syntax.
 */
class ExtendedLegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
    const PRIORITY = 101;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
    {
        if (!$this->loadDca($container->getName(), $this->getDispatcher()))
        {
            return;
        }

        $this->parseBasicDefinition($container);
        $this->parseDataProvider($container);
        $this->parsePalettes($container);
        $this->parseConditions($container);
        $this->parseBackendView($container);
        $this->parseClassNames($container);
        $this->loadAdditionalDefinitions($container, $event);
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
        if (!$container->hasBasicDefinition())
        {
            $config = new DefaultBasicDefinition();
            $container->setBasicDefinition($config);
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
        if (($providers = $this->getFromDca('dca_config/data_provider')) !== null)
        {
            $this->getDispatcher()->addListener(
                sprintf('%s[%s]', PopulateEnvironmentEvent::NAME, $container->getName()),
                function (PopulateEnvironmentEvent $event) {
                    $environment = $event->getEnvironment();
                    $definition  = $environment->getDataDefinition();
                    $parentName  = $definition->getBasicDefinition()->getParentDataProvider();

                    if ($parentName)
                    {
                        $factory          = DcGeneralFactory::deriveEmptyFromEnvironment($environment)->setContainerName($parentName);
                        $parentDefinition = $factory->createContainer();

                        $environment->setParentDataDefinition($parentDefinition);
                    }

                    $rootName = $definition->getBasicDefinition()->getRootDataProvider();
                    if ($rootName)
                    {

                        $factory        = DcGeneralFactory::deriveEmptyFromEnvironment($environment)->setContainerName($rootName);
                        $rootDefinition = $factory->createContainer();

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
        if ($container->hasDefinition(ExtendedDca::NAME))
        {
            $definition = $container->getDefinition(ExtendedDca::NAME);

            if (!($definition instanceof ExtendedDca))
            {
                throw new DcGeneralInvalidArgumentException(sprintf(
                    'Definition with name %s must be an instance of ExtendedDca but instance of %s encountered.',
                    ExtendedDca::NAME,
                    get_class($definition)
                ));
            }
        }
        else
        {
            $definition = new ExtendedDca();
            $container->setDefinition(ExtendedDca::NAME, $definition);
        }

        if ($this->getFromDca('dca_config') === null)
        {
            return;
        }

        if (($class = $this->getFromDca('dca_config/controller')) !== null)
        {
            $definition->setControllerClass($class);
        }

        if (($class = $this->getFromDca('dca_config/view')) !== null)
        {
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
        return in_array($name, array('default', 'root', 'parent'));
    }

    /**
     * Parse a single data provider information and prepare the definition object for it.
     *
     * @param ContainerInterface              $container   The container where the data shall be stored.
     *
     * @param DataProviderDefinitionInterface $providers   The data provider container.
     *
     * @param array                           $information The information for the data provider to be parsed.
     *
     * @param string|null                     $name        The name of the data provider to be used within the container.
     *
     * @return ContaoDataProviderInformation|null
     */
    protected function parseSingleDataProvider(
        ContainerInterface $container,
        DataProviderDefinitionInterface $providers,
        array $information,
        $name
    )
    {
        if (isset($information['factory']))
        {
            $factoryClass        = new \ReflectionClass($information['factory']);
            $factory             = $factoryClass->newInstance();
            $providerInformation = $factory->build($information);
        }
        else
        {
            // Determine the name.
            if ($name && !$this->isSpecialName($name))
            {
                $providerName = $name;
            }
            elseif ($name === 'default')
            {
                $providerName = $container->getName();
            }
            elseif (isset($information['source']))
            {
                $providerName = $information['source'];
            }
            else
            {
                $providerName = $container->getName();
            }

            // Check config if it already exists, if not, add it.
            if (!$providers->hasInformation($providerName))
            {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($providerName);
                $providers->addInformation($providerInformation);
            }
            else
            {
                $providerInformation = $providers->getInformation($providerName);
            }

            if (!$providerInformation->getTableName())
            {
                if (isset($information['source']))
                {
                    $providerInformation
                        ->setTableName($information['source']);
                }
                else
                {
                    $providerInformation
                        ->setTableName($providerName);
                }
            }

            if (isset($information['class']))
            {
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
     */
    protected function parseDataProvider(ContainerInterface $container)
    {
        // Parse data provider.
        if ($container->hasDataProviderDefinition())
        {
            $config = $container->getDataProviderDefinition();
        }
        else
        {
            $config = new DefaultDataProviderDefinition();
            $container->setDataProviderDefinition($config);
        }

        // First check if we are using the "new" notation used in DcGeneral 0.9.
        if (!is_array($this->getFromDca('dca_config/data_provider')))
        {
            return;
        }

        $dataProvidersDca = $this->getFromDca('dca_config/data_provider');

        foreach ($dataProvidersDca as $dataProviderDcaName => $dataProviderDca)
        {
            $providerInformation = $this->parseSingleDataProvider($container, $config, $dataProviderDca, $dataProviderDcaName);

            if ($providerInformation instanceof ContaoDataProviderInformation)
            {
                $initializationData     = (array)$providerInformation->getInitializationData();
                $baseInitializationData = array(
                    'name' => $dataProviderDcaName,
                );

                switch ((string)$dataProviderDcaName)
                {
                    case 'default':
                        $providerInformation->setVersioningEnabled(
                            (bool)$this->getFromDca('config/enableVersioning')
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

                $providerInformation->setInitializationData(array_merge(
                    $baseInitializationData,
                    $dataProviderDca,
                    $initializationData
                ));
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
        if (!is_callable($palettesDca))
        {
            return;
        }

        if ($container->hasDefinition(PalettesDefinitionInterface::NAME))
        {
            $palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
        }
        else
        {
            $palettesDefinition = new DefaultPalettesDefinition();
            $container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
        }

        call_user_func($palettesDca, $palettesDefinition, $container);
    }

    /**
     * Parse the root condition.
     *
     * @param ContainerInterface                   $container  The container where the data shall be stored.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship definition.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If no root data provider is defined.
     */
    protected function parseRootCondition(
        ContainerInterface $container,
        ModelRelationshipDefinitionInterface $definition
    )
    {
        if (($rootCondition = $this->getFromDca('dca_config/rootEntries')) !== null)
        {
            $rootProvider = $container->getBasicDefinition()->getRootDataProvider();

            if (!$rootProvider)
            {
                throw new DcGeneralRuntimeException(
                    'Root data provider name not specified in DCA but rootEntries section specified.'
                );
            }

            if (!$container->getDataProviderDefinition()->hasInformation($rootProvider))
            {
                throw new DcGeneralRuntimeException('Unknown root data provider but rootEntries section specified.');
            }

            if (isset($rootCondition[$rootProvider]))
            {
                $rootCondition = $rootCondition[$rootProvider];
                $mySetter      = $rootCondition['setOn'];

                if (($relationship = $definition->getRootCondition()) === null)
                {
                    $relationship = new RootCondition();
                    $setter       = $mySetter;
                    $builder      = FilterBuilder::fromArrayForRoot()->getFilter();
                }
                else
                {
                    /** @var RootConditionInterface $relationship */
                    if ($relationship->getSetters())
                    {
                        $setter = array_merge_recursive($mySetter, $relationship->getSetters());
                    }
                    else
                    {
                        $setter = $mySetter;
                    }
                    $builder = FilterBuilder::fromArrayForRoot($relationship->getFilterArray())->getFilter();
                }

                $builder->append(FilterBuilder::fromArrayForRoot((array)$rootCondition['filter']));

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
    )
    {
        if (($childConditions = $this->getFromDca('dca_config/childCondition')) !== null)
        {
            foreach ((array)$childConditions as $childCondition)
            {
                /** @var ParentChildConditionInterface $relationship */
                $relationship = $definition->getChildCondition($childCondition['from'], $childCondition['to']);
                if (!$relationship instanceof ParentChildConditionInterface)
                {
                    $relationship = new ParentChildCondition();
                    $relationship
                        ->setSourceName($childCondition['from'])
                        ->setDestinationName($childCondition['to']);
                    $definition->addChildCondition($relationship);
                    $setter  = $childCondition['setOn'];
                    $inverse = $childCondition['inverse'];
                }
                else
                {
                    $setter  = array_merge_recursive((array)$childCondition['setOn'], $relationship->getSetters());
                    $inverse = array_merge_recursive((array)$childCondition['inverse'], $relationship->getInverseFilterArray());
                }

                $relationship
                    ->setFilterArray(
                        FilterBuilder::fromArray($relationship->getFilterArray())
                            ->getFilter()
                            ->append(
                                FilterBuilder::fromArray((array)$childCondition['filter'])
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
        if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME))
        {
            $definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        }
        else
        {
            $definition = new DefaultModelRelationshipDefinition();
            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
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
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME))
        {
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        }
        else
        {
            $view = new Contao2BackendViewDefinition();
            $container->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $view);
        }

        if (!$view instanceof Contao2BackendViewDefinitionInterface)
        {
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
        $listing = $view->getListingConfig();

        $this->parseListLabel($listing);
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
        if (($formats = $this->getFromDca('dca_config/child_list')) === null)
        {
            return;
        }

        foreach ($formats as $providerName => $format)
        {
            $formatter  = new DefaultModelFormatterConfig();
            $configured = false;

            if (isset($format['fields']))
            {
                $formatter->setPropertyNames($format['fields']);
                $configured = true;
            }

            if (isset($format['format']))
            {
                $formatter->setFormat($format['format']);
                $configured = true;
            }

            if (isset($format['maxCharacters']))
            {
                $formatter->setMaxLength($format['maxCharacters']);
                $configured = true;
            }

            if ($configured)
            {
                $listing->setLabelFormatter($providerName, $formatter);
            }
        }
    }
}
