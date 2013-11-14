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

use DcGeneral\Contao\Callbacks\ContainerOnDeleteCallbackListener;
use DcGeneral\Contao\Callbacks\ContainerOnSubmitCallbackListener;
use DcGeneral\Contao\Callbacks\StaticCallbackListener;
use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Contao\Dca\Palette\LegacyPalettesParser;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use DcGeneral\DataDefinition\Definition\Palette\DefaultProperty;
use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use DcGeneral\DataDefinition\Definition\View\Command;
use DcGeneral\DataDefinition\Definition\View\CommandInterface;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Contao\View\Contao2BackendView\LabelFormatter;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

		$this->parseCallbacks($container, $event->getDispatcher());
		$this->parseBasicDefinition($container);
		$this->parseDataProvider($container);
		$this->parseRootEntries($container);
		$this->parseParentChildConditions($container);
		$this->parseBackendView($container);
		$this->parsePalettes($container);
		$this->parseProperties($container);
	}

	/**
	 * Parse the basic configuration and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseCallbacks(ContainerInterface $container, EventDispatcher $dispatcher)
	{
		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/onload_callback')) !== null)
		{
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new StaticCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/onsubmit_callback')) !== null)
		{
			// TODO use the submit related event here
			/*
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new ContainerOnSubmitCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
			*/
		}

		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/ondelete_callback')) !== null)
		{
			// TODO use the submit related event here
			/*
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new ContainerOnDeleteCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
			*/
		}

		/*
		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/onload_callback')) !== null)
		{
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new StaticCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/onload_callback')) !== null)
		{
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new StaticCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && ($value = $this->getFromDca('config/onload_callback')) !== null)
		{
			$dispatcher->addListener(
				CreateDcGeneralEvent::NAME,
				new StaticCallbackListener($value, $GLOBALS['objDcGeneral'])
			);
		}
		*/
	}

	/**
	 * Parse the basic configuration and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
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

		// TODO need to be documented or moved
		if (($switchToEdit = $this->getFromDca('config/switchToEdit')) !== null)
		{
			$config->setSwitchToEditEnabled((bool) $switchToEdit);
		}

		if (($value = $this->getFromDca('config/closed')) !== null)
		{
			$config->setClosed((bool) $value);
		}

		if (($value = $this->getFromDca('config/notEditable')) !== null)
		{
			$config->setEditable(!$value);
		}

		if (($value = $this->getFromDca('config/notDeletable')) !== null)
		{
			$config->setDeletable(!$value);
		}

		if (($value = $this->getFromDca('config/notCreatable')) !== null)
		{
			$config->setCreatable(!(bool) $value);
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

	/**
	 * This method parses the root entries definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseRootEntries(ContainerInterface $container)
	{
		// TODO to be implemented
	}

	/**
	 * This method parses the parent-child conditions.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseParentChildConditions(ContainerInterface $container)
	{
		// TODO to be implemented
	}

	/**
	 * Parse and build the backend view definition for the old Contao2 backend view.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
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
		$this->parsePanel($container, $view);
		$this->parseGlobalOperations($container, $view);
		$this->parseModelOperations($container, $view);
	}

	/**
	 * Parse the listing configuration.
	 *
	 * @param ContainerInterface $container
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
	 * Parse the defined palettes and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parsePanel(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
	{
		$layout = $view->getPanelLayout();
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

	/**
	 * Parse the defined container scoped operations and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseGlobalOperations(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
	{
		$operationsDca = $this->getFromDca('list/global_operations');

		if (!is_array($operationsDca)) {
			return;
		}

		$collection = $view->getGlobalCommands();

		foreach ($operationsDca as $operationName => $operationDca) {
			$command = $this->createCommand($operationName, $operationsDca[$operationName]);
			$collection->addCommand($command);
		}
	}

	/**
	 * Parse the defined model scoped operations and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseModelOperations(ContainerInterface $container, Contao2BackendViewDefinitionInterface $view)
	{
		$operationsDca = $this->getFromDca('list/operations');

		if (!is_array($operationsDca)) {
			return;
		}

		$collection = $view->getModelCommands();

		foreach ($operationsDca as $operationName => $operationDca) {
			$command = $this->createCommand($operationName, $operationsDca[$operationName]);
			$collection->addCommand($command);
		}
	}

	/**
	 * Parse the defined palettes and populate the definition.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parsePalettes(ContainerInterface $container)
	{
		$palettesDefinitionArray = $this->getFromDca('palettes');
		$subPalettesDefinitionArray = $this->getFromDca('subpalettes');

		// skip while there is no legacy palette definition
		if (!is_array($palettesDefinitionArray)) {
			return;
		}

		// ignore non-legacy sub palette definition
		if (!is_array($subPalettesDefinitionArray)) {
			$subPalettesDefinitionArray = array();
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
			$palettesDefinitionArray,
			$subPalettesDefinitionArray,
			$palettesDefinition
		);
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
	 * Create a command from dca.
	 *
	 * @param string $commandName
	 * @param array $commandDca
	 *
	 * @return CommandInterface
	 */
	protected function createCommand($commandName, array $commandDca)
	{
		$command = new Command();
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
				$label = reset($lang);
				$description = next($lang);

				$command->setDescription($description);
			}
			else {
				$label = $lang;
			}

			$command->setLabel($label);

			unset($commandDca['label']);
		}

		if (isset($commandDca['description'])) {
			$command->setDescription($commandDca['description']);

			unset($commandDca['description']);
		}

		if (isset($commandDca['button_callback'])) {
			// TODO handle callback

			unset($commandDca['button_callback']);
		}

		if (count($commandDca)) {
			$extra = $command->getExtra();

			foreach ($commandDca as $name => $value) {
				$extra[$name] = $value;
			}
		}

		return $command;
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
