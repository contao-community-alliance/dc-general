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
use DcGeneral\Contao\Dca\Palette\LegacyPalettesParser;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Definition\BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\DataDefinition\Definition\DefaultBackendViewDefinition;
use DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use DcGeneral\DataDefinition\Definition\Palette\DefaultProperty;
use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\View\BackendView\LabelFormatter;

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
	public function build(ContainerInterface $container, BuildDataDefinitionEvent $event)
	{
		if (!$this->loadDca($container->getName()))
		{
			return;
		}

		$this->parseBasicDefinition($container);
		$this->parseListing($container);
		$this->parseProperties($container);
		$this->parsePalettes($container);
		$this->parseDataProvider($container);
		$this->parsePanel($container);
	}

	protected function getBackendViewDefinition(ContainerInterface $container)
	{
		if ($container->hasDefinition(BackendViewDefinitionInterface::NAME))
		{
			$config = $container->getDefinition(BackendViewDefinitionInterface::NAME);
		}
		else
		{
			$config = new DefaultBackendViewDefinition();
			$container->setDefinition(BackendViewDefinitionInterface::NAME, $config);
		}

		if (!$config instanceof BackendViewDefinitionInterface)
		{
			throw new DcGeneralInvalidArgumentException('Configured BackendViewDefinition does not implement BackendViewDefinitionInterface.');
		}

		return $config;
	}

	protected function parseBasicDefinition(ContainerInterface $container)
	{
		// parse data provider
		if ($container->hasBasicDefinition())
		{
			$config = $container->getBasicDefinition();
		}
		else
		{
			$config = new DefaultBasicDefinition();
			$container->setBasicDefinition($config);
		}

		switch ($this->getFromDca('list/sorting/mode'))
		{
			case 0: // Records are not sorted
			case 1: // Records are sorted by a fixed field
			case 2: // Records are sorted by a switchable field
			case 3: // Records are sorted by the parent table
				$config->setMode(BasicDefinitionInterface::MODE_FLAT);
				break;
			case 4: // Displays the child records of a parent record (see style sheets module)
				$config->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
				break;
			case 5: // Records are displayed as tree (see site structure)
			case 6: // Displays the child records within a tree structure (see articles module)
				$config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
				break;
			default:
		}

		if (($switchToEdit = $this->getFromDca('config/switchToEdit')) !== null)
		{
			$config->setSwitchToEditEnabled((bool) $switchToEdit);
		}
	}

	/**
	 * Parse the listing configuration.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseListing(ContainerInterface $container)
	{
		$view = $this->getBackendViewDefinition($container);
		$listing = $view->getListingConfig();

		$listDca = $this->getFromDca('list');

		// cancel if no list configuration found
		if (!$listDca) {
			return;
		}

		$this->parseListSorting($listing, $listDca);
		$this->parseListLabel($listing, $listDca);
	}

	/**
	 * Parse the sorting part of listing configuration.
	 *
	 * @param ListingConfigInterface $listing
	 *
	 * @return void
	 */
	protected function parseListSorting(ListingConfigInterface $listing, array $listDca)
	{
		$sortingDca = isset($listDca['sorting']) ? $listDca['sorting'] : array();

		if (isset($sortingDca['flag'])) {
			$this->evalFlag($listing, $sortingDca['flag']);
		}

		if (isset($sortingDca['fields'])) {
			$fields = array();

			foreach ($sortingDca['fields'] as $field) {
				if (preg_match('~^(\w+)(?: (ASC|DESC))?$~', $field, $matches)) {
					$fields[$matches[1]] = isset($matches[2]) ? $matches[2] : 'ASC';
				}
				else {
					throw new DcGeneralRuntimeException('Custom SQL in sorting fields are currently unsupported');
				}
			}

			$listing->setDefaultSortingFields($fields);
		}

		if (isset($sortingDca['headerFields'])) {
			$listing->setHeaderPropertyNames((array) $sortingDca['headerFields']);
		}

		if (isset($sortingDca['icon'])) {
			$listing->setRootIcon($sortingDca['icon']);
		}

		if (isset($sortingDca['disableGrouping']) && $sortingDca['disableGrouping']) {
			$listing->setGroupingMode(ListingConfigInterface::GROUP_NONE);
		}

		if (isset($sortingDca['child_record_class'])) {
			$listing->setItemCssClass($sortingDca['child_record_class']);
		}
	}

	/**
	 * Parse the sorting part of listing configuration.
	 *
	 * @param ListingConfigInterface $listing
	 *
	 * @return void
	 */
	protected function parseListLabel(ListingConfigInterface $listing, array $listDca)
	{
		$labelDca   = isset($listDca['label']) ? $listDca['label'] : array();

		$formatter  = new LabelFormatter();
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
			$formatter->setMaxLenght($labelDca['maxCharacters']);
			$configured = true;
		}

		if ($configured) {
			$listing->setLabelFormatter($formatter);
		}
	}

	/**
	 * Parse the defined properties and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseProperties(ContainerInterface $container)
	{
		// parse data provider
		if ($container->hasPropertiesDefinition())
		{
			$definition = $container->getPropertiesDefinition();
		}
		else
		{
			$definition = new DefaultPropertiesDefinition();
			$container->setPropertiesDefinition($definition);
		}

		foreach ($this->getFromDca('fields') as $propName => $propInfo)
		{
			if ($definition->hasProperty($propName))
			{
				$property = $definition->getProperty($propName);
			}
			else
			{
				$property = new DefaultProperty($propName);
				$definition->addProperty($property);
			}

			if (!$property->getLabel() && isset($propInfo['label']))
			{
				$lang = $propInfo['label'];

				if (is_array($lang)) {
					$label       = reset($lang);
					$description = next($lang);

					$property->setDescription($description);
				}
				else {
					$label = $lang;
				}

				$property->setLabel($label);
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
				$this->evalFlag($property, $propInfo['flag']);
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
		if ($container->hasDataProviderDefinition())
		{
			$config = $container->getDataProviderDefinition();
		}
		else
		{
			$config = new DefaultDataProviderDefinition();
			$container->setDataProviderDefinition($config);
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

				$container->getBasicDefinition()->setRootDataProvider($parentTable);
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

			$container->getBasicDefinition()->setDataProvider($container->getName());
		}
	}

	protected function parsePanel(ContainerInterface $container)
	{
		$config = $this->getBackendViewDefinition($container);

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
		$subPalettesDefinition = $this->getFromDca('subpalettes');

		// skip while there is no legacy palette definition
		if (!is_array($palettesDefinition)) {
			return;
		}

		// ignore non-legacy subpalette definition
		if (!is_array($subPalettesDefinition)) {
			$subPalettesDefinition = array();
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

		$palettesParser = new LegacyPalettesParser();
		$palettesParser->parse(
			$palettesDefinition,
			$subPalettesDefinition,
			$palettesDefinition
		);
	}

	/**
	 * Evaluate the contao 2 sorting flag into sorting mode, grouping mode and grouping length.
	 *
	 * @param ListingConfigInterface|PropertyInterface $config
	 * @param int $flag
	 */
	protected function evalFlag($config, $flag)
	{
		switch ($flag) {
			// Sort by initial letter ascending
			// Aufsteigende Sortierung nach Anfangsbuchstabe
			case 1:
			// Sort by initial two letters ascending
			// Aufsteigende Sortierung nach den ersten beiden Buchstaben
			case 3:
			// Sort by day ascending
			// Aufsteigende Sortierung nach Tag
			case 5:
			// Sort by month ascending
			// Aufsteigende Sortierung nach Monat
			case 7:
			// Sort by year ascending
			// Aufsteigende Sortierung nach Jahr
			case 9:
			// Sort ascending
			// Aufsteigende Sortierung
			case 11:
				$config->setSortingMode(ListingConfigInterface::SORT_ASC);
				break;
			// Sort by initial letter descending
			// Absteigende Sortierung nach Anfangsbuchstabe
			case 2:
			// Sort by initial two letters descending
			// Absteigende Sortierung nach den ersten beiden Buchstaben
			case 4:
			// Sort by day descending
			// Absteigende Sortierung nach Tag
			case 6:
			// Sort by month descending
			// Absteigende Sortierung nach Monat
			case 8:
			// Sort by year descending
			// Absteigende Sortierung nach Jahr
			case 10:
			// Sort descending
			// Absteigende Sortierung
			case 12:
				$config->setSortingMode(ListingConfigInterface::SORT_DESC);
				break;
		}

		switch ($flag) {
			// Sort by initial letter ascending
			// Aufsteigende Sortierung nach Anfangsbuchstabe
			case 1:
			// Sort by initial letter descending
			// Absteigende Sortierung nach Anfangsbuchstabe
			case 2:
			// Sort by initial two letters ascending
			// Aufsteigende Sortierung nach den ersten beiden Buchstaben
			case 3:
			// Sort by initial two letters descending
			// Absteigende Sortierung nach den ersten beiden Buchstaben
			case 4:
				$config->setGroupingMode(ListingConfigInterface::GROUP_CHAR);
				break;
			// Sort by day ascending
			// Aufsteigende Sortierung nach Tag
			case 5:
			// Sort by day descending
			// Absteigende Sortierung nach Tag
			case 6:
				$config->setGroupingMode(ListingConfigInterface::GROUP_DAY);
				break;
			// Sort by month ascending
			// Aufsteigende Sortierung nach Monat
			case 7:
			// Sort by month descending
			// Absteigende Sortierung nach Monat
			case 8:
				$config->setGroupingMode(ListingConfigInterface::GROUP_MONTH);
				break;
			// Sort by year ascending
			// Aufsteigende Sortierung nach Jahr
			case 9:
			// Sort by year descending
			// Absteigende Sortierung nach Jahr
			case 10:
				$config->setGroupingMode(ListingConfigInterface::GROUP_YEAR);
				break;
			// Sort ascending
			// Aufsteigende Sortierung
			case 11:
			// Sort descending
			// Absteigende Sortierung
			case 12:
				$config->setGroupingMode(ListingConfigInterface::GROUP_NONE);
				break;
		}

		switch ($flag) {
			// Sort by initial letter ascending
			// Aufsteigende Sortierung nach Anfangsbuchstabe
			case 1:
			// Sort by initial letter descending
			// Absteigende Sortierung nach Anfangsbuchstabe
			case 2:
				$config->setGroupingLength(1);
				break;
			// Sort by initial two letters ascending
			// Aufsteigende Sortierung nach den ersten beiden Buchstaben
			case 3:
			// Sort by initial two letters descending
			// Absteigende Sortierung nach den ersten beiden Buchstaben
			case 4:
				$config->setGroupingLength(2);
				break;
		}
	}
}
