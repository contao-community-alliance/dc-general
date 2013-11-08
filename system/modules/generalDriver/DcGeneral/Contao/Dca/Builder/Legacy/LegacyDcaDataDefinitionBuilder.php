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
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Section\BackendViewSectionInterface;
use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\DataDefinition\Section\DefaultBackendViewSection;
use DcGeneral\DataDefinition\Section\DefaultBasicSection;
use DcGeneral\DataDefinition\Section\DefaultDataProviderSection;
use DcGeneral\DataDefinition\Section\DefaultPalettesSection;
use DcGeneral\DataDefinition\Section\Palette\DefaultProperty;
use DcGeneral\DataDefinition\Section\PalettesSectionInterface;
use DcGeneral\DataDefinition\Section\DefaultPropertiesSection;
use DcGeneral\DataDefinition\Section\View\Panel\DefaultFilterElementInformation;
use DcGeneral\DataDefinition\Section\View\Panel\DefaultLimitElementInformation;
use DcGeneral\DataDefinition\Section\View\Panel\DefaultSearchElementInformation;
use DcGeneral\DataDefinition\Section\View\Panel\DefaultSortElementInformation;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Build the container config from legacy DCA syntax.
 */
class LegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
	const PRIORITY = 100;

	protected $dca;

	/**
	 * {@inheritDoc}
	 */
	public function build(ContainerInterface $container)
	{
		if (!$this->loadDca($container->getName()))
		{
			return;
		}

		$this->parseBasicSection($container);
		$this->parseProperties($container);
		$this->parsePalettes($container);
		$this->parseDataProvider($container);
		$this->parsePanel($container);
	}

	protected function getBackendViewSection(ContainerInterface $container)
	{
		if ($container->hasSection(BackendViewSectionInterface::NAME))
		{
			$config = $container->getSection(BackendViewSectionInterface::NAME);
		}
		else
		{
			$config = new DefaultBackendViewSection();
			$container->setSection(BackendViewSectionInterface::NAME, $config);
		}

		if (!$config instanceof BackendViewSectionInterface)
		{
			throw new DcGeneralInvalidArgumentException('Configured BackendViewSection does not implement BackendViewSectionInterface.');
		}

		return $config;
	}

	protected function parseBasicSection(ContainerInterface $container)
	{
		// parse data provider
		if ($container->hasBasicSection())
		{
			$config = $container->getBasicSection();
		}
		else
		{
			$config = new DefaultBasicSection();
			$container->setBasicSection($config);
		}

		switch ($this->getFromDca('list/sorting/mode'))
		{
			case 0: // Records are not sorted
			case 1: // Records are sorted by a fixed field
			case 2: // Records are sorted by a switchable field
			case 3: // Records are sorted by the parent table
				$config->setMode(BasicSectionInterface::MODE_FLAT);
				break;
			case 4: // Displays the child records of a parent record (see style sheets module)
				$config->setMode(BasicSectionInterface::MODE_PARENTEDLIST);
				break;
			case 5: // Records are displayed as tree (see site structure)
			case 6: // Displays the child records within a tree structure (see articles module)
				$config->setMode(BasicSectionInterface::MODE_HIERARCHICAL);
				break;
			default:
		}

		if (($switchToEdit = $this->getFromDca('config/switchToEdit')) !== null)
		{
			$config->setSwitchToEditEnabled((bool) $switchToEdit);
		}
	}

	/**
	 * Parse the defined properties and populate the section.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseProperties(ContainerInterface $container)
	{
		// parse data provider
		if ($container->hasPropertiesSection())
		{
			$section = $container->getPropertiesSection();
		}
		else
		{
			$section = new DefaultPropertiesSection();
			$container->setPropertiesSection($section);
		}

		foreach ($this->getFromDca('fields') as $propName => $propInfo)
		{
			if ($section->hasProperty($propName))
			{
				$property = $section->getProperty($propName);
			}
			else
			{
				$property = new DefaultProperty($propName);
				$section->addProperty($property);
			}

			if (!$property->getLabel() && isset($propInfo['label']))
			{
				$property->setLabel($propInfo['label']);
			}

			if (!$property->getDescription() && isset($propInfo['description']))
			{
				$property->setDescription($propInfo['description']);
			}

			if (!$property->getDefaultValue() && isset($propInfo['default']))
			{
				$property->setDefaultValue($propInfo['default']);
			}

			if (isset($propInfo['exclude']))
			{
				$property->setExcluded($propInfo['exclude']);
			}

			if (isset($propInfo['search']))
			{
				$property->setSearchable($propInfo['search']);
			}

			if (isset($propInfo['sorting']))
			{
				$property->setSortable($propInfo['sorting']);
			}

			if (isset($propInfo['filter']))
			{
				$property->setFilterable($propInfo['filter']);
			}

			if (isset($propInfo['flag']))
			{
				if (!$property->getGroupingMode())
				{
					// // TODO: determine grouping mode here
					$property->setGroupingMode($propInfo['flag']);
				}
				if (!$property->getSortingMode())
				{
					// // TODO: determine sorting mode here
					$property->getSortingMode($propInfo['flag']);
				}
			}

			if (!$property->getGroupingLength() && isset($propInfo['length']))
			{
				$property->setGroupingLength($propInfo['length']);
			}

			if (!$property->getWidgetType() && isset($propInfo['inputType']))
			{
				$property->setWidgetType($propInfo['inputType']);
			}

			if (!$property->getOptions() && isset($propInfo['options']))
			{
				$property->setOptions($propInfo['options']);
			}

			if (!$property->getExplanation() && isset($propInfo['explanation']))
			{
				$property->setExplanation($propInfo['explanation']);
			}

			if (!$property->getExtra() && isset($propInfo['eval']))
			{
				$property->setExtra($propInfo['eval']);
			}

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
		if ($container->hasDataProviderSection())
		{
			$config = $container->getDataProviderSection();
		}
		else
		{
			$config = new DefaultDataProviderSection();
			$container->setDataProviderSection($config);
		}

		if (($parentTable = $this->getFromDca('config/ptable')) !== null)
		{
			// Check config if it already exists, if not, add it.
			if (!$config->hasInformation($parentTable))
			{
				$providerInformation = new ContaoDataProviderInformation();
				$providerInformation->setName($parentTable);
				$config->addInformation($providerInformation);
			}
			else
			{
				$providerInformation = $config->getInformation($parentTable);
			}

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				$providerInformation
					->setTableName($parentTable)
					->setInitializationData(array(
						'source' => $container->getName()
					));

				$container->getBasicSection()->setRootDataProvider($parentTable);
			}
		}

		// Check config if it already exists, if not, add it.
		if (!$config->hasInformation($container->getName()))
		{
			$providerInformation = new ContaoDataProviderInformation();
			$providerInformation->setName($container->getName());
			$config->addInformation($providerInformation);
		}
		else
		{
			$providerInformation = $config->getInformation($container->getName());
		}

		if ($providerInformation instanceof ContaoDataProviderInformation)
		{
			$providerInformation
				->setTableName($container->getName())
				->setInitializationData(array(
					'source' => $container->getName()
				))
				->isVersioningEnabled((bool)$this->getFromDca('config/enableVersioning'));

			$container->getBasicSection()->setDataProvider($container->getName());
		}
	}

	protected function parsePanel(ContainerInterface $container)
	{
		$config = $this->getBackendViewSection($container);

		$layout = $config->getPanelLayout();
		$rows = $layout->getRows();

		foreach (explode(';', (string)$this->getFromDca('list/sorting/panelLayout')) as $rowNo => $elementRow)
		{
			if ($rows->getRowCount() < $rowNo+1)
			{
				$row = $rows->addRow();
			}
			else
			{
				$row = $rows->getRow($rowNo);
			}

			foreach (explode(',', $elementRow) as $element)
			{
				switch ($element)
				{
					case 'filter':
						foreach ($this->getFromDca('fields') as $property => $value)
						{
							if (isset($value['filter']))
							{
								$element = new DefaultFilterElementInformation();
								$element->setPropertyName($property);
								if (!$row->hasElement($element->getName()))
								{
									$row->addElement($element);
								}
							}
						}
						continue;
					case 'sort':
						if ($row->hasElement('sort'))
						{
							$element = $row->getElement('sort');
						}
						else
						{
							$element = new DefaultSortElementInformation();
							$row->addElement($element);
						}

						foreach ($this->getFromDca('fields') as $property => $value)
						{
							if (isset($value['sorting']))
							{
								$element->addProperty($property, (int)$value['flag']);
							}
						}
						continue;
					case 'search':
						if ($row->hasElement('search'))
						{
							$element = $row->getElement('search');
						}
						else
						{
							$element = new DefaultSearchElementInformation();
						}
						foreach ($this->getFromDca('fields') as $property => $value)
						{
							if (isset($value['search']))
							{
								$element->addProperty($property);
							}
						}
						if ($element->getPropertyNames() && !$row->hasElement('search'))
						{
							$row->addElement($element);
						}
						continue;
					case 'limit':
						if (!$row->hasElement('limit'))
						{
							$row->addElement(new DefaultLimitElementInformation());
						}
						continue;
				}
			}
		}
	}

	protected function parsePalettes(ContainerInterface $container)
	{
		$palettesDefinition = $this->getFromDca('palettes');

		// There is no legacy palette definition
		if (!is_array($palettesDefinition)) {
			return;
		}

		if ($container->hasSection(PalettesSectionInterface::NAME))
		{
			$palettesSection = $container->getSection(PalettesSectionInterface::NAME);
		}
		else
		{
			$palettesSection = new DefaultPalettesSection();
			$container->setSection(PalettesSectionInterface::NAME, $palettesSection);
		}

		foreach ($palettesDefinition as $paletteSelector => $paletteFields) {

		}
	}
}
