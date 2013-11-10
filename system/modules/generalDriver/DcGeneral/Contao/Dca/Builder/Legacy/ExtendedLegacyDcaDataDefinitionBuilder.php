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

use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Contao\Dca\Definition\ExtendedDca;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
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
			}

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				// Set versioning information.
				$providerInformation
					->setTableName($providerName)
					->setInitializationData($dataProviderDca);

				// TODO: add additional information here.
				switch ($dataProviderDcaName) {
					case 'default':
						$providerInformation->isVersioningEnabled(
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
}
