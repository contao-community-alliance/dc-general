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

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Real implementation of DC_General for Contao 2.11 and Contao 3.1 - will get replaced with composer based auto loading in the future.
	'DcGeneral\View\ViewTemplateInterface'                     => 'system/modules/generalDriver/DcGeneral/View/ViewTemplateInterface.php',
	'DcGeneral\View\DefaultView'                               => 'system/modules/generalDriver/DcGeneral/View/DefaultView.php',
	'DcGeneral\View\ViewInterface'                             => 'system/modules/generalDriver/DcGeneral/View/ViewInterface.php',
	'DcGeneral\View\View'                                      => 'system/modules/generalDriver/DcGeneral/View/View.php',
	'DcGeneral\View\ContaoBackendViewTemplate'                 => 'system/modules/generalDriver/DcGeneral/View/ContaoBackendViewTemplate.php',
	'DcGeneral\Callbacks\PhpNativeCallbacks'                   => 'system/modules/generalDriver/DcGeneral/Callbacks/PhpNativeCallbacks.php',
	'DcGeneral\Callbacks\Callbacks'                            => 'system/modules/generalDriver/DcGeneral/Callbacks/Callbacks.php',
	'DcGeneral\Callbacks\CallbacksInterface'                   => 'system/modules/generalDriver/DcGeneral/Callbacks/CallbacksInterface.php',
	'DcGeneral\Callbacks\ContaoStyleCallbacks'                 => 'system/modules/generalDriver/DcGeneral/Callbacks/ContaoStyleCallbacks.php',
	'DcGeneral\DataDefinition\BaseCondition'                   => 'system/modules/generalDriver/DcGeneral/DataDefinition/BaseCondition.php',
	'DcGeneral\DataDefinition\RootConditionInterface'          => 'system/modules/generalDriver/DcGeneral/DataDefinition/RootConditionInterface.php',
	'DcGeneral\DataDefinition\PropertyInterface'               => 'system/modules/generalDriver/DcGeneral/DataDefinition/PropertyInterface.php',
	'DcGeneral\DataDefinition\ContainerInterface'              => 'system/modules/generalDriver/DcGeneral/DataDefinition/ContainerInterface.php',
	'DcGeneral\DataDefinition\OperationInterface'              => 'system/modules/generalDriver/DcGeneral/DataDefinition/OperationInterface.php',
	'DcGeneral\DataDefinition\ParentChildConditionInterface'   => 'system/modules/generalDriver/DcGeneral/DataDefinition/ParentChildConditionInterface.php',
	'DcGeneral\DataDefinition\AbstractCondition'               => 'system/modules/generalDriver/DcGeneral/DataDefinition/AbstractCondition.php',
	'DcGeneral\DataDefinition\ConditionInterface'              => 'system/modules/generalDriver/DcGeneral/DataDefinition/ConditionInterface.php',
	'DcGeneral\EnvironmentInterface'                           => 'system/modules/generalDriver/DcGeneral/EnvironmentInterface.php',
	'DcGeneral\Clipboard\ClipboardInterface'                   => 'system/modules/generalDriver/DcGeneral/Clipboard/ClipboardInterface.php',
	'DcGeneral\Clipboard\BaseClipboard'                        => 'system/modules/generalDriver/DcGeneral/Clipboard/BaseClipboard.php',
	'DcGeneral\Clipboard\DefaultClipboard'                     => 'system/modules/generalDriver/DcGeneral/Clipboard/DefaultClipboard.php',
	'DcGeneral\Helper\WidgetAccessor'                          => 'system/modules/generalDriver/DcGeneral/Helper/WidgetAccessor.php',
	'DcGeneral\Controller\Ajax'                                => 'system/modules/generalDriver/DcGeneral/Controller/Ajax.php',
	'DcGeneral\Controller\Controller'                          => 'system/modules/generalDriver/DcGeneral/Controller/Controller.php',
	'DcGeneral\Controller\Interfaces\Controller'               => 'system/modules/generalDriver/DcGeneral/Controller/Interfaces/Controller.php',
	'DcGeneral\Controller\Ajax2X'                              => 'system/modules/generalDriver/DcGeneral/Controller/Ajax2X.php',
	'DcGeneral\Controller\Ajax3X'                              => 'system/modules/generalDriver/DcGeneral/Controller/Ajax3X.php',
	'DcGeneral\Controller\ControllerInterface'                 => 'system/modules/generalDriver/DcGeneral/Controller/ControllerInterface.php',
	'DcGeneral\Controller\DefaultController'                   => 'system/modules/generalDriver/DcGeneral/Controller/DefaultController.php',
	'DcGeneral\DC_General'                                     => 'system/modules/generalDriver/DcGeneral/DC_General.php',
	'DcGeneral\InputProviderInterface'                         => 'system/modules/generalDriver/DcGeneral/InputProviderInterface.php',
	'DcGeneral\Panel\DefaultPanel'                             => 'system/modules/generalDriver/DcGeneral/Panel/DefaultPanel.php',
	'DcGeneral\Panel\DefaultSortElement'                       => 'system/modules/generalDriver/DcGeneral/Panel/DefaultSortElement.php',
	'DcGeneral\Panel\BaseFilterElement'                        => 'system/modules/generalDriver/DcGeneral/Panel/BaseFilterElement.php',
	'DcGeneral\Panel\DefaultLimitElement'                      => 'system/modules/generalDriver/DcGeneral/Panel/DefaultLimitElement.php',
	'DcGeneral\Panel\PanelTemplateInterface'                   => 'system/modules/generalDriver/DcGeneral/Panel/PanelTemplateInterface.php',
	'DcGeneral\Panel\FilterElementInterface'                   => 'system/modules/generalDriver/DcGeneral/Panel/FilterElementInterface.php',
	'DcGeneral\Panel\BaseSortElement'                          => 'system/modules/generalDriver/DcGeneral/Panel/BaseSortElement.php',
	'DcGeneral\Panel\BasePanel'                                => 'system/modules/generalDriver/DcGeneral/Panel/BasePanel.php',
	'DcGeneral\Panel\DefaultSubmitElement'                     => 'system/modules/generalDriver/DcGeneral/Panel/DefaultSubmitElement.php',
	'DcGeneral\Panel\SubmitElementInterface'                   => 'system/modules/generalDriver/DcGeneral/Panel/SubmitElementInterface.php',
	'DcGeneral\Panel\PanelInterface'                           => 'system/modules/generalDriver/DcGeneral/Panel/PanelInterface.php',
	'DcGeneral\Panel\AbstractElement'                          => 'system/modules/generalDriver/DcGeneral/Panel/AbstractElement.php',
	'DcGeneral\Panel\SortElementInterface'                     => 'system/modules/generalDriver/DcGeneral/Panel/SortElementInterface.php',
	'DcGeneral\Panel\BaseLimitElement'                         => 'system/modules/generalDriver/DcGeneral/Panel/BaseLimitElement.php',
	'DcGeneral\Panel\LimitElementInterface'                    => 'system/modules/generalDriver/DcGeneral/Panel/LimitElementInterface.php',
	'DcGeneral\Panel\BaseSearchElement'                        => 'system/modules/generalDriver/DcGeneral/Panel/BaseSearchElement.php',
	'DcGeneral\Panel\SearchElementInterface'                   => 'system/modules/generalDriver/DcGeneral/Panel/SearchElementInterface.php',
	'DcGeneral\Panel\DefaultPanelContainer'                    => 'system/modules/generalDriver/DcGeneral/Panel/DefaultPanelContainer.php',
	'DcGeneral\Panel\PanelElementInterface'                    => 'system/modules/generalDriver/DcGeneral/Panel/PanelElementInterface.php',
	'DcGeneral\Panel\PanelContainerInterface'                  => 'system/modules/generalDriver/DcGeneral/Panel/PanelContainerInterface.php',
	'DcGeneral\Panel\DefaultSearchElement'                     => 'system/modules/generalDriver/DcGeneral/Panel/DefaultSearchElement.php',
	'DcGeneral\Panel\DefaultFilterElement'                     => 'system/modules/generalDriver/DcGeneral/Panel/DefaultFilterElement.php',
	'DcGeneral\Data\DefaultDriver'                             => 'system/modules/generalDriver/DcGeneral/Data/DefaultDriver.php',
	'DcGeneral\Data\AbstractModel'                             => 'system/modules/generalDriver/DcGeneral/Data/AbstractModel.php',
	'DcGeneral\Data\DefaultConfig'                             => 'system/modules/generalDriver/DcGeneral/Data/DefaultConfig.php',
	'DcGeneral\Data\DriverInterface'                           => 'system/modules/generalDriver/DcGeneral/Data/DriverInterface.php',
	'DcGeneral\Data\DefaultCollection'                         => 'system/modules/generalDriver/DcGeneral/Data/DefaultCollection.php',
	'DcGeneral\Data\MultiLanguageDriverInterface'              => 'system/modules/generalDriver/DcGeneral/Data/MultiLanguageDriverInterface.php',
	'DcGeneral\Data\MultiLanguageDriver'                       => 'system/modules/generalDriver/DcGeneral/Data/MultiLanguageDriver.php',
	'DcGeneral\Data\ConfigInterface'                           => 'system/modules/generalDriver/DcGeneral/Data/ConfigInterface.php',
	'DcGeneral\Data\ModelInterface'                            => 'system/modules/generalDriver/DcGeneral/Data/ModelInterface.php',
	'DcGeneral\Data\DCGE'                                      => 'system/modules/generalDriver/DcGeneral/Data/DCGE.php',
	'DcGeneral\Data\CollectionInterface'                       => 'system/modules/generalDriver/DcGeneral/Data/CollectionInterface.php',
	'DcGeneral\Data\Collection'                                => 'system/modules/generalDriver/DcGeneral/Data/Collection.php',
	'DcGeneral\Data\Config'                                    => 'system/modules/generalDriver/DcGeneral/Data/Config.php',
	'DcGeneral\Data\DefaultModel'                              => 'system/modules/generalDriver/DcGeneral/Data/DefaultModel.php',
	'DcGeneral\Data\Model'                                     => 'system/modules/generalDriver/DcGeneral/Data/Model.php',
	'DcGeneral\Data\TableRowsAsRecordsDriver'                  => 'system/modules/generalDriver/DcGeneral/Data/TableRowsAsRecordsDriver.php',
	'DcGeneral\Contao\InputProvider'                           => 'system/modules/generalDriver/DcGeneral/Contao/InputProvider.php',
	'DcGeneral\Contao\Dca\Container'                           => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Container.php',
	'DcGeneral\Contao\Dca\Property'                            => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Property.php',
	'DcGeneral\Contao\Dca\Operation'                           => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Operation.php',
	'DcGeneral\Contao\Dca\Conditions\ParentChildCondition'     => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Conditions/ParentChildCondition.php',
	'DcGeneral\Contao\Dca\Conditions\RootCondition'            => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Conditions/RootCondition.php',
	'DcGeneral\Contao\BackendBindings'                         => 'system/modules/generalDriver/DcGeneral/Contao/BackendBindings.php',
	'DcGeneral\DefaultEnvironment'                             => 'system/modules/generalDriver/DcGeneral/DefaultEnvironment.php',
	'DcGeneral\DataContainerInterface'                         => 'system/modules/generalDriver/DcGeneral/DataContainerInterface.php',
	'DcGeneral\BaseEnvironment'                                => 'system/modules/generalDriver/DcGeneral/BaseEnvironment.php',

	// FIXME: we can not deprecate this class as the only way for Contao is to load the class from root namespace.
	'DC_General'                                                => 'system/modules/generalDriver/DC_General.php',

	// Backwards compatibility layer from here on. DEPRECATED only, DO NOT USE.
	'GeneralDataMultiLanguageDefault'                          => 'system/modules/generalDriver/GeneralDataMultiLanguageDefault.php',
	'GeneralControllerDefault'                                 => 'system/modules/generalDriver/GeneralControllerDefault.php',
	'AbstractGeneralModel'                                     => 'system/modules/generalDriver/AbstractGeneralModel.php',
	'InterfaceGeneralCallback'                                 => 'system/modules/generalDriver/InterfaceGeneralCallback.php',
	'GeneralAjax2X'                                            => 'system/modules/generalDriver/GeneralAjax2X.php',
	'GeneralAjax'                                              => 'system/modules/generalDriver/GeneralAjax.php',
	'InterfaceGeneralModel'                                    => 'system/modules/generalDriver/InterfaceGeneralModel.php',
	'GeneralCollectionDefault'                                 => 'system/modules/generalDriver/GeneralCollectionDefault.php',
	'InterfaceGeneralController'                               => 'system/modules/generalDriver/InterfaceGeneralController.php',
	'GeneralDataDefault'                                       => 'system/modules/generalDriver/GeneralDataDefault.php',
	'InterfaceGeneralCollection'                               => 'system/modules/generalDriver/InterfaceGeneralCollection.php',
	'InterfaceGeneralDataMultiLanguage'                        => 'system/modules/generalDriver/InterfaceGeneralDataMultiLanguage.php',
	'GeneralDataTableRowsAsRecords'                            => 'system/modules/generalDriver/GeneralDataTableRowsAsRecords.php',
	'GeneralDataConfigDefault'                                 => 'system/modules/generalDriver/GeneralDataConfigDefault.php',
	'GeneralAjax3X'                                            => 'system/modules/generalDriver/GeneralAjax3X.php',
	'GeneralViewDefault'                                       => 'system/modules/generalDriver/GeneralViewDefault.php',
	'WidgetAccessor'                                           => 'system/modules/generalDriver/WidgetAccessor.php',
	'InterfaceGeneralDataConfig'                               => 'system/modules/generalDriver/InterfaceGeneralDataConfig.php',
	'DCGE'                                                     => 'system/modules/generalDriver/DCGE.php',
	'InterfaceGeneralData'                                     => 'system/modules/generalDriver/InterfaceGeneralData.php',
	'InterfaceGeneralView'                                     => 'system/modules/generalDriver/InterfaceGeneralView.php',
	'GeneralCallbackDefault'                                   => 'system/modules/generalDriver/GeneralCallbackDefault.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'dcbe_general_edit'             => 'system/modules/generalDriver/templates',
	'dcbe_general_field'            => 'system/modules/generalDriver/templates',
	'dcbe_general_language_panel'   => 'system/modules/generalDriver/templates',
	'dcbe_general_listView'         => 'system/modules/generalDriver/templates',
	'dcbe_general_listView_sorting' => 'system/modules/generalDriver/templates',
	'dcbe_general_panel'            => 'system/modules/generalDriver/templates',
	'dcbe_general_panel_filter'     => 'system/modules/generalDriver/templates',
	'dcbe_general_panel_limit'      => 'system/modules/generalDriver/templates',
	'dcbe_general_panel_search'     => 'system/modules/generalDriver/templates',
	'dcbe_general_panel_sort'       => 'system/modules/generalDriver/templates',
	'dcbe_general_panel_submit'     => 'system/modules/generalDriver/templates',
	'dcbe_general_parentView'       => 'system/modules/generalDriver/templates',
	'dcbe_general_show'             => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview'         => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_child'   => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_entry'   => 'system/modules/generalDriver/templates',
	'dcbe_general_breadcrumb'       => 'system/modules/generalDriver/templates',
));
