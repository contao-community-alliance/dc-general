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

use DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener;
use DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener;
use DcGeneral\Contao\Callback\ContainerHeaderCallbackListener;
use DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener;
use DcGeneral\Contao\Callback\ContainerOnCutCallbackListener;
use DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener;
use DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener;
use DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener;
use DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener;
use DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener;
use DcGeneral\Contao\Callback\ModelChildRecordCallbackListener;
use DcGeneral\Contao\Callback\ModelGroupCallbackListener;
use DcGeneral\Contao\Callback\ModelLabelCallbackListener;
use DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener;
use DcGeneral\Contao\Callback\ModelOptionsCallbackListener;
use DcGeneral\Contao\Callback\PropertyInputFieldCallbackListener;
use DcGeneral\Contao\Callback\PropertyInputFieldGetWizardCallbackListener;
use DcGeneral\Contao\Callback\PropertyOnLoadCallbackListener;
use DcGeneral\Contao\Callback\PropertyOnSaveCallbackListener;
use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Contao\Dca\Palette\LegacyPalettesParser;
use DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use DcGeneral\DataDefinition\Definition\View\Command;
use DcGeneral\DataDefinition\Definition\View\CommandInterface;
use DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\DefaultSubmitElementInformation;
use DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use DcGeneral\DataDefinition\Definition\View\PanelRowInterface;
use DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use DcGeneral\Event\PostDeleteModelEvent;
use DcGeneral\Event\PostDuplicateModelEvent;
use DcGeneral\Event\PostPasteModelEvent;
use DcGeneral\Event\PostPersistModelEvent;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\CreateDcGeneralEvent;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;
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
		$this->loadAdditionalDefinitions($container, $event);
	}

	/**
	 * Load additional definitions, like naming of parent data provider.
	 *
	 * This method will register an event to the populate environment event in which the parent data provider container
	 * will get loaded.
	 *
	 * @param ContainerInterface       $container The container where the data shall be stored.
	 *
	 * @param BuildDataDefinitionEvent $event     The event being emitted.
	 *
	 * @return void
	 */
	protected function loadAdditionalDefinitions(ContainerInterface $container, BuildDataDefinitionEvent $event)
	{
		if ($this->getFromDca('config/ptable'))
		{
			$event->getDispatcher()->addListener(
				sprintf('%s[%s]', PopulateEnvironmentEvent::NAME, $container->getName()),
				function (PopulateEnvironmentEvent $event) {
					$environment      = $event->getEnvironment();
					$definition       = $environment->getDataDefinition();
					$parentName       = $definition->getBasicDefinition()->getParentDataProvider();
					$factory          = DcGeneralFactory::deriveEmptyFromEnvironment($environment)->setContainerName($parentName);
					$parentDefinition = $factory->createContainer();

					$environment->setParentDataDefinition($parentDefinition);
				}
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
	protected function parseCallbacks(ContainerInterface $container, EventDispatcherInterface $dispatcher)
	{
		if (isset($GLOBALS['objDcGeneral']) && is_array($callbacks = $this->getFromDca('config/onload_callback')))
		{
			foreach ($callbacks as $callback)
			{
				$dispatcher->addListener(
					sprintf('%s[%s]', CreateDcGeneralEvent::NAME, $container->getName()),
					new ContainerOnLoadCallbackListener($callback, $GLOBALS['objDcGeneral'])
				);
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && is_array($callbacks = $this->getFromDca('config/onsubmit_callback')))
		{
			foreach ($callbacks as $callback)
			{
				$dispatcher->addListener(
					sprintf('%s[%s]', PostPersistModelEvent::NAME, $container->getName()),
					new ContainerOnSubmitCallbackListener($callback, $GLOBALS['objDcGeneral'])
				);
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && is_array($callbacks = $this->getFromDca('config/ondelete_callback')))
		{
			foreach ($callbacks as $callback)
			{
				$dispatcher->addListener(
					sprintf('%s[%s]', PostDeleteModelEvent::NAME, $container->getName()),
					new ContainerOnDeleteCallbackListener($callback, $GLOBALS['objDcGeneral'])
				);
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && is_array($callbacks = $this->getFromDca('config/oncut_callback')))
		{
			foreach ($callbacks as $callback)
			{
				$dispatcher->addListener(
					sprintf('%s[%s]', PostPasteModelEvent::NAME, $container->getName()),
					new ContainerOnCutCallbackListener($callback, $GLOBALS['objDcGeneral'])
				);
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && is_array($callbacks = $this->getFromDca('config/oncopy_callback')))
		{
			foreach ($callbacks as $callback)
			{
				$dispatcher->addListener(
					sprintf('%s[%s]', PostDuplicateModelEvent::NAME, $container->getName()),
					new ContainerOnCopyCallbackListener($callback, $GLOBALS['objDcGeneral'])
				);
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/sorting/header_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', GetParentHeaderEvent::NAME, $container->getName()),
				new ContainerHeaderCallbackListener($callback, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/sorting/paste_button_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', GetPasteRootButtonEvent::NAME, $container->getName()),
				new ContainerPasteRootButtonCallbackListener($callback, $GLOBALS['objDcGeneral'])
			);
			$dispatcher->addListener(
				sprintf('%s[%s]', GetPasteButtonEvent::NAME, $container->getName()),
				new ContainerPasteButtonCallbackListener($callback, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/sorting/child_record_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', ParentViewChildRecordEvent::NAME, $container->getName()),
				new ModelChildRecordCallbackListener($callback)
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/label/group_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', GetGroupHeaderEvent::NAME, $container->getName()),
				new ModelGroupCallbackListener($callback)
			);
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/label/label_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', ModelToLabelEvent::NAME, $container->getName()),
				new ModelLabelCallbackListener($callback, $GLOBALS['objDcGeneral'])
			);
		}

		if (isset($GLOBALS['objDcGeneral']))
		{
			if (is_array($operations = $this->getFromDca('global_operations')))
			{
				foreach ($operations as $operationName => $operationInfo)
				{
					if (isset($operationInfo['button_callback']))
					{
						$callback = $operationInfo['button_callback'];
						$dispatcher->addListener(
							sprintf('%s[%s][%s]', GetGlobalButtonEvent::NAME, $container->getName(), $operationName),
							new ContainerGlobalButtonCallbackListener($callback)
						);
					}
				}
			}
		}

		if (isset($GLOBALS['objDcGeneral']))
		{
			if (is_array($operations = $this->getFromDca('operations')))
			{
				foreach ($operations as $operationName => $operationInfo)
				{
					if (isset($operationInfo['button_callback']))
					{
						$callback = $operationInfo['button_callback'];
						$dispatcher->addListener(
							sprintf('%s[%s][%s]', GetOperationButtonEvent::NAME, $container->getName(), $operationName),
							new ModelOperationButtonCallbackListener($callback)
						);
					}
				}
			}
		}

		if (isset($GLOBALS['objDcGeneral']))
		{
			foreach ($this->getFromDca('fields') as $propName => $propInfo)
			{
				if (isset($propInfo['load_callback']))
				{
					foreach ($propInfo['load_callback'] as $callback)
					{
						$dispatcher->addListener(
							DecodePropertyValueForWidgetEvent::NAME . sprintf('[%s][%s]', $container->getName(), $propName),
							new PropertyOnLoadCallbackListener($callback, $GLOBALS['objDcGeneral'])
						);
					}
				}

				if (isset($propInfo['save_callback']))
				{
					foreach ($propInfo['save_callback'] as $callback)
					{
						$dispatcher->addListener(
							EncodePropertyValueFromWidgetEvent::NAME . sprintf('[%s][%s]', $container->getName(), $propName),
							new PropertyOnSaveCallbackListener($callback, $GLOBALS['objDcGeneral'])
						);
					}
				}

				if (isset($propInfo['options_callback']))
				{
					$callback = $propInfo['options_callback'];
					$dispatcher->addListener(
						GetPropertyOptionsEvent::NAME . sprintf('[%s][%s]', $container->getName(), $propName),
						new ModelOptionsCallbackListener($callback, $GLOBALS['objDcGeneral'])
					);
				}

				if (isset($propInfo['input_field_callback']))
				{
					$callback = $propInfo['input_field_callback'];
					$dispatcher->addListener(
						BuildWidgetEvent::NAME . sprintf('[%s][%s]', $container->getName(), $propName),
						new PropertyInputFieldCallbackListener($callback, $GLOBALS['objDcGeneral'])
					);
				}

				if (isset($propInfo['wizard']))
				{
					$callback = $propInfo['wizard'];
					$dispatcher->addListener(
						ManipulateWidgetEvent::NAME . sprintf('[%s][%s]', $container->getName(), $propName),
						new PropertyInputFieldGetWizardCallbackListener($callback, $GLOBALS['objDcGeneral'])
					);
				}
			}
		}

		if (isset($GLOBALS['objDcGeneral']) && $callback = $this->getFromDca('list/presentation/breadcrumb_callback'))
		{
			$dispatcher->addListener(
				sprintf('%s[%s]', GetBreadcrumbEvent::NAME, $container->getName()),
				new ContainerGetBreadcrumbCallbackListener($callback, $GLOBALS['objDcGeneral'])
			);
		}
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
		switch ($this->getFromDca('list/sorting/mode'))
		{
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
		if (($switchToEdit = $this->getFromDca('config/switchToEdit')) !== null)
		{
			$config->setSwitchToEditEnabled((bool)$switchToEdit);
		}

		if (($value = $this->getFromDca('config/closed')) !== null)
		{
			$config->setClosed((bool)$value);
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
		if ($container->hasBasicDefinition())
		{
			$config = $container->getBasicDefinition();
		}
		else
		{
			$config = new DefaultBasicDefinition();
			$container->setBasicDefinition($config);
		}

		$this->parseBasicMode($config);
		$this->parseBasicFlags($config);

		if (($filters = $this->getFromDca('list/sorting/filter')) !== null)
		{
			if (is_array($filters) && !empty($filters))
			{
				$myFilters = array();
				foreach ($filters as $filter)
				{
					// FIXME: this only takes array('name', 'value') into account. Add support for: array('name=?', 'value').
					$myFilters = array('operation' => '=', 'property' => $filter[0], 'value' => $filter[1]);
				}
				if ($config->hasAdditionalFilter())
				{
					$currentFilter = $config->getAdditionalFilter();
					$currentFilter = array_merge($currentFilter, $myFilters);
					$filter        = array(
						'operation' => 'AND',
						'children'  => array($currentFilter)
					);
				}
				else
				{
					$filter = $myFilters;
				}

				$config->setAdditionalFilter($config->getDataProvider(), $filter);
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
				$container->getBasicDefinition()->setParentDataProvider($parentTable);
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
	 * @param ContainerInterface $container The container where the data shall be stored.
	 *
	 * @return void
	 */
	protected function parseRootEntries(ContainerInterface $container)
	{
		// FIXME: to be implemented.
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
		if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME))
		{
			$definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
		}
		else
		{
			$definition = new DefaultModelRelationshipDefinition();
		}

		// If ptable defined and no root setter we need to add (Contao default id=>pid mapping).
		if (($value = $this->getFromDca('config/ptable')) !== null)
		{
			$rootProvider = $this->getRootProviderName($container);

			if (($relationship = $definition->getRootCondition()) === null)
			{
				$relationship = new RootCondition();
				$relationship
					->setSourceName($rootProvider);
				$definition->setRootCondition($relationship);
			}
			if (!$relationship->getSetters())
			{
				$relationship
					->setSetters(array(array('pid' => 'id')));
			}

			$container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
		}

		// If root id defined, add condition to root filter for id=?.
		if (($value = $this->getFromDca('list/sorting/root')) !== null)
		{
			$rootProvider = $this->getRootProviderName($container);

			$myFilter = array('operation' => '=', 'property' => 'id', 'value' => $value);

			if (($relationship = $definition->getRootCondition()) === null)
			{
				$relationship = new RootCondition();
				$filter       = $myFilter;
			}
			else
			{
				$filter   = $relationship->getFilterArray();
				$filter[] = $myFilter;
				$filter   = array(
					'operation' => 'AND',
					'children' => array($filter)
				);
			}

			$relationship
				->setSourceName($rootProvider)
				->setFilterArray($filter);
			$definition->setRootCondition($relationship);

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

		$this->parseListing($container, $view);
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

		// Cancel if no list configuration found.
		if (!$listDca)
		{
			return;
		}

		$this->parseListSorting($listing, $listDca);
		$this->parseListLabel($container, $listing, $listDca);
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

		if (isset($sortingDca['flag']))
		{
			$this->evalFlag($listing, $sortingDca['flag']);
		}

		if (isset($sortingDca['fields']))
		{
			$fields = array();

			foreach ($sortingDca['fields'] as $field)
			{
				if (preg_match('~^(\w+)(?: (ASC|DESC))?$~', $field, $matches))
				{
					$fields[$matches[1]] = isset($matches[2]) ? $matches[2] : 'ASC';
				}
				else
				{
					throw new DcGeneralRuntimeException('Custom SQL in sorting fields are currently unsupported');
				}
			}

			$listing->setDefaultSortingFields($fields);
		}

		if (isset($sortingDca['headerFields']))
		{
			$listing->setHeaderPropertyNames((array)$sortingDca['headerFields']);
		}

		if (isset($sortingDca['icon']))
		{
			$listing->setRootIcon($sortingDca['icon']);
		}

		if (isset($sortingDca['disableGrouping']) && $sortingDca['disableGrouping'])
		{
			$listing->setGroupingMode(ListingConfigInterface::GROUP_NONE);
		}

		if (isset($sortingDca['child_record_class']))
		{
			$listing->setItemCssClass($sortingDca['child_record_class']);
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

		if (isset($labelDca['fields']))
		{
			$formatter->setPropertyNames($labelDca['fields']);
			$configured = true;
		}

		if (isset($labelDca['format']))
		{
			$formatter->setFormat($labelDca['format']);
			$configured = true;
		}

		if (isset($labelDca['maxCharacters']))
		{
			$formatter->setMaxLength($labelDca['maxCharacters']);
			$configured = true;
		}

		if ($configured)
		{
			$listing->setLabelFormatter($container->getBasicDefinition()->getDataProvider(), $formatter);
		}

		if (isset($labelDca['showColumns']))
		{
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
		if (!$row->hasElement('limit'))
		{
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
		if (!$row->hasElement('submit'))
		{
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
		foreach (explode(',', $elementList) as $element)
		{
			switch ($element)
			{
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

		foreach (explode(';', (string)$this->getFromDca('list/sorting/panelLayout')) as $rowNo => $elementRow)
		{
			if ($rows->getRowCount() < ($rowNo + 1))
			{
				$row = $rows->addRow();
			}
			else
			{
				$row = $rows->getRow($rowNo);
			}

			$this->parsePanelRow($row, $elementRow);

			if ($row->getCount() == 0)
			{
				$rows->deleteRow($rowNo);
			}
		}

		$hasSubmit = false;
		foreach ($rows as $row)
		{
			foreach ($row as $element)
			{
				if ($element instanceof SubmitElementInformationInterface)
				{
					$hasSubmit = true;
					break;
				}

				if ($hasSubmit)
				{
					break;
				}
			}
		}

		if (!$hasSubmit && $rows->getRowCount())
		{
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

		if (!is_array($operationsDca))
		{
			return;
		}

		$collection = $view->getGlobalCommands();

		foreach ($operationsDca as $operationName => $operationDca)
		{
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

		if (!is_array($operationsDca))
		{
			return;
		}

		$collection = $view->getModelCommands();

		foreach ($operationsDca as $operationName => $operationDca)
		{
			$command = $this->createCommand($operationName, $operationsDca[$operationName]);
			$collection->addCommand($command);
		}
	}

	/**
	 * Parse the defined palettes and populate the definition.
	 *
	 * @param ContainerInterface $container The container where the data shall be stored.
	 *
	 * @return void
	 */
	protected function parsePalettes(ContainerInterface $container)
	{
		$palettesDefinitionArray    = $this->getFromDca('palettes');
		$subPalettesDefinitionArray = $this->getFromDca('subpalettes');

		// Skip while there is no legacy palette definition.
		if (!is_array($palettesDefinitionArray))
		{
			return;
		}

		// Ignore non-legacy sub palette definition.
		if (!is_array($subPalettesDefinitionArray))
		{
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
		if (!$property->getLabel())
		{
			if (is_array($label))
			{
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
		foreach ($propInfo as $key => $value)
		{
			switch ($key)
			{
				case 'label':
					$this->parseSinglePropertyLabel($property, $value);
					break;

				case 'description':
					if (!$property->getDescription())
					{
						$property->setDescription($value);
					}
					break;

				case 'default':
					if (!$property->getDefaultValue())
					{
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
					$property->setExtra($value);
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

			$this->parseSingleProperty($property, $propInfo);
		}
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
		$command = new Command();
		$command->setName($commandName);

		$parameters = $command->getParameters();

		if (isset($commandDca['href']))
		{
			parse_str($commandDca['href'], $queryParameters);
			foreach ($queryParameters as $name => $value)
			{
				$parameters[$name] = $value;
			}
			unset($commandDca['href']);
		}

		if (isset($commandDca['parameters']))
		{
			foreach ($commandDca['parameters'] as $name => $value)
			{
				$parameters[$name] = $value;
			}
			unset($commandDca['parameters']);
		}

		if (isset($commandDca['label']))
		{
			$lang = $commandDca['label'];

			if (is_array($lang))
			{
				$label       = reset($lang);
				$description = next($lang);

				$command->setDescription($description);
			}
			else {
				$label = $lang;
			}

			$command->setLabel($label);

			unset($commandDca['label']);
		}

		if (isset($commandDca['description']))
		{
			$command->setDescription($commandDca['description']);

			unset($commandDca['description']);
		}

		// Callback is transformed into event in parseCallbacks().
		if (isset($commandDca['button_callback']))
		{
			unset($commandDca['button_callback']);
		}

		if (count($commandDca))
		{
			$extra = $command->getExtra();

			foreach ($commandDca as $name => $value)
			{
				$extra[$name] = $value;
			}
		}

		return $command;
	}

	/**
	 * Evaluate the contao 2 sorting flag into sorting mode.
	 *
	 * @param ListingConfigInterface|PropertyInterface $config The property to evaluate the flag for.
	 *
	 * @param int                                      $flag   The flag to be evaluated.
	 *
	 * @return void
	 */
	protected function evalFlagSorting($config, $flag)
	{
		if (($flag < 0) || ($flag > 12))
		{
			return;
		}

		if (($flag % 2) == 1)
		{
			$config->setSortingMode(ListingConfigInterface::SORT_ASC);
		}
		else
		{
			$config->setSortingMode(ListingConfigInterface::SORT_DESC);
		}
	}

	/**
	 * Evaluate the contao 2 sorting flag into grouping mode.
	 *
	 * @param ListingConfigInterface|PropertyInterface $config The property to evaluate the flag for.
	 *
	 * @param int                                      $flag   The flag to be evaluated.
	 *
	 * @return void
	 */
	protected function evalFlagGrouping($config, $flag)
	{
		if (($flag < 0) || ($flag > 12))
		{
			return;
		}

		if ($flag <= 4)
		{
			$config->setGroupingMode(ListingConfigInterface::GROUP_CHAR);
		}
		elseif ($flag <= 6)
		{
			$config->setGroupingMode(ListingConfigInterface::GROUP_DAY);
		}
		elseif ($flag <= 8)
		{
			$config->setGroupingMode(ListingConfigInterface::GROUP_MONTH);
		}
		elseif ($flag <= 10)
		{
			$config->setGroupingMode(ListingConfigInterface::GROUP_YEAR);
		}
		else
		{
			$config->setGroupingMode(ListingConfigInterface::GROUP_NONE);
		}
	}

	/**
	 * Evaluate the contao 2 sorting flag into grouping length.
	 *
	 * @param ListingConfigInterface|PropertyInterface $config The property to evaluate the flag for.
	 *
	 * @param int                                      $flag   The flag to be evaluated.
	 *
	 * @return void
	 */
	protected function evalFlagGroupingLength($config, $flag)
	{
		if (($flag == 1) || ($flag == 2))
		{
			$config->setGroupingLength(1);
		}
		elseif(($flag == 3) || ($flag == 4))
		{
			$config->setGroupingLength(2);
		}
	}

	/**
	 * Evaluate the contao 2 sorting flag into sorting mode, grouping mode and grouping length.
	 *
	 * @param ListingConfigInterface|PropertyInterface $config The property to evaluate the flag for.
	 *
	 * @param int                                      $flag   The flag to be evaluated.
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
