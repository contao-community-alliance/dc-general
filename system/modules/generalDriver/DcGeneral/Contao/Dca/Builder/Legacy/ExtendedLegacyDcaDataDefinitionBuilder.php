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
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Build the container config from legacy DCA syntax.
 */
class ExtendedLegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
	const PRIORITY = 100;

	protected $dca;

	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
	{
		if (!$this->loadDca($container->getName()))
		{
			return;
		}

		// TODO parse $localDca variable into $container
		$this->parseDataProvider($container);
		$this->parsePalettes($container);
		$this->parseConditions($container);
		$this->parseBackendView($container);
	}

	protected function parseClassNames(ContainerInterface $container)
	{
		// parse data provider
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

		if (($class = $this->getFromDca('dca_config/callback')) === null)
		{
			$definition->setCallbackClass($class);
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
	 * This method parses all data provider related information from Contao legacy data container arrays.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseDataProvider(ContainerInterface $container)
	{
		// parse data provider
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
			if (isset($dataProviderDca['factory'])) {
				$factoryClass = new \ReflectionClass($dataProviderDca['factory']);
				$factory = $factoryClass->newInstance();
				$providerInformation = $factory->build($dataProviderDca);
			}
			else {
				// Determine the name.
				if (isset($dataProviderDca['source']))
				{
					$providerName = $dataProviderDca['source'];
				}
				else
				{
					$providerName = $container->getName();
				}

				// Check config if it already exists, if not, add it.
				if (!$config->hasInformation($providerName))
				{
					$providerInformation = new ContaoDataProviderInformation();
					$providerInformation->setName($providerName);
					$config->addInformation($providerInformation);
				}
				else
				{
					$providerInformation = $config->getInformation($providerName);
				}

				if (!$providerInformation->getTableName()) {
					$providerInformation
						->setTableName($providerName);
				}
			}

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				// Set versioning information.
				$providerInformation
					->setInitializationData($dataProviderDca);

				if (isset($dataProviderDca['class'])) {
					$providerInformation->setClassName($dataProviderDca['class']);
				}

				// TODO: add additional information here.
				switch ($dataProviderDcaName) {
					case 'default':
						$providerInformation->setVersioningEnabled(
							(bool) $this->getFromDca('config/enableVersioning')
						);

						$container->getBasicDefinition()->setDataProvider($providerInformation->getName());
						break;

					case 'root':
						$container->getBasicDefinition()->setRootDataProvider($providerInformation->getName());
						break;

					case 'parent':
						$container->getBasicDefinition()->setParentDataProvider($providerInformation->getName());
						break;
				}
			}
		}
	}

	protected function parsePalettes(ContainerInterface $container)
	{
		$palettesDca = $this->getFromDca('palettes');

		// skip while there is no extended palette definition
		if (!is_callable($palettesDca)) {
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
			$rootCondition = $rootCondition[$rootProvider];

			$relationship = new RootCondition();
			$relationship
				->setSourceName($rootProvider)
				->setFilterArray($rootCondition['filter'])
				->setSetters($rootCondition['setOn']);
			$definition->setRootCondition($relationship);
		}

		if (($childConditions = $this->getFromDca('dca_config/childCondition')) !== null)
		{
			foreach ((array) $childConditions as $childCondition)
			{
				$relationship = new ParentChildCondition();
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
	 * @param ContainerInterface $container
	 *
	 * @return void
	 *
	 * @throws DcGeneralInvalidArgumentException
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
			throw new DcGeneralInvalidArgumentException('Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.');
		}

		$this->parseListing($container, $view);
	}

	/**
	 * Parse the listing configuration.
	 *
	 * @param ContainerInterface                    $container
	 *
	 * @param Contao2BackendViewDefinitionInterface $view
	 *
	 * @return void
	 */
	protected function parseListing(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
	{
		$listing = $view->getListingConfig();

		$listDca = $this->getFromDca('list');

		// cancel if no list configuration found
		if (!$listDca) {
			return;
		}

		$this->parseListLabel($container, $listing, $listDca);
	}

	/**
	 * Parse the sorting part of listing configuration.
	 *
	 * @param \DcGeneral\DataDefinition\ContainerInterface $container
	 *
	 * @param ListingConfigInterface                       $listing
	 *
	 * @param array                                        $listDca
	 *
	 * @return void
	 */
	protected function parseListLabel(ContainerInterface $container, ListingConfigInterface $listing, array $listDca)
	{
		if (($formats = $this->getFromDca('dca_config/child_list')) === null)
		{
			return;
		}

		foreach ($formats as $providerName => $format)
		{
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
}
