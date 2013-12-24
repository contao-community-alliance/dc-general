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

namespace DcGeneral\Contao\Dca\Builder\Legacy;

use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Contao\Dca\Definition\ExtendedDca;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Build the container config from legacy DCA syntax.
 */
class ExtendedLegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
	const PRIORITY = 100;

	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
	{
		if (!$this->loadDca($container->getName()))
		{
			return;
		}

		$this->parseDataProvider($container);
		$this->parsePalettes($container);
		$this->parseConditions($container);
		$this->parseBackendView($container);
		$this->loadAdditionalDefinitions($container, $event);
	}

	/**
	 * Load all additional definitions, like naming of parent data provider etc.
	 *
	 * @param ContainerInterface       $container The container where the data shall be stored.
	 *
	 * @param BuildDataDefinitionEvent $event     The event being emitted.
	 *
	 * @return void
	 */
	protected function loadAdditionalDefinitions(ContainerInterface $container, BuildDataDefinitionEvent $event)
	{
		if (($providers = $this->getFromDca('dca_config/data_provider')) !== null)
		{
			$event->getDispatcher()->addListener(
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

		if (($class = $this->getFromDca('dca_config/controller')) === null)
		{
			$definition->setControllerClass($class);
		}

		if (($class = $this->getFromDca('dca_config/view')) === null)
		{
			$definition->setViewClass($class);
		}
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
	 * @return ContaoDataProviderInformation|null
	 */
	protected function parseSingleDataProvider(
		ContainerInterface $container,
		DataProviderDefinitionInterface $providers,
		array $information
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
			if (isset($information['source']))
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
				$providerInformation
					->setTableName($providerName);
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
			$providerInformation = $this->parseSingleDataProvider($container, $config, $dataProviderDca);

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				// Set versioning information.
				$providerInformation
					->setInitializationData($dataProviderDca);

				if (isset($information['class']))
				{
					$providerInformation->setClassName($information['class']);
				}

				switch ($dataProviderDcaName)
				{
					case 'default':
						$providerInformation->setVersioningEnabled(
							(bool)$this->getFromDca('config/enableVersioning')
						);

						$container->getBasicDefinition()->setDataProvider($providerInformation->getName());
						break;

					case 'root':
						$container->getBasicDefinition()->setRootDataProvider($providerInformation->getName());
						break;

					case 'parent':
						$container->getBasicDefinition()->setParentDataProvider($providerInformation->getName());
						break;

					default:
				}
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

				$myFilter = $rootCondition['filter'];
				$mySetter = $rootCondition['setOn'];

				if (($relationship = $definition->getRootCondition()) === null)
				{
					$relationship = new RootCondition();
					$setter       = $mySetter;
					$filter       = $myFilter;
				}
				else
				{
					/** @var \DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface $relationship */
					if ($relationship->getSetters())
					{
						$setter = array_merge_recursive($mySetter, $relationship->getSetters());
					}
					else
					{
						$setter = $mySetter;
					}
					$filter = array_merge($relationship->getFilterArray(), $myFilter);
					$filter = array(
						'operation' => 'AND',
						'children' => array($filter)
					);
				}

				$relationship
					->setSourceName($rootProvider)
					->setFilterArray($filter)
					->setSetters($setter);
				$definition->setRootCondition($relationship);
			}
		}

		if (($childConditions = $this->getFromDca('dca_config/childCondition')) !== null)
		{
			foreach ((array)$childConditions as $childCondition)
			{
				$relationship = new ParentChildCondition();
				/** @var \DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface $relationship */
				$relationship
					->setSourceName($childCondition['from'])
					->setDestinationName($childCondition['to'])
					->setFilterArray($childCondition['filter'])
					->setSetters($childCondition['setOn'])
					->setInverseFilterArray($childCondition['inverse']);
				$definition->addChildCondition($relationship);
			}
		}
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
