<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
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
	'DcGeneral\BaseEnvironment'                                 => 'system/modules/generalDriver/DcGeneral/BaseEnvironment.php',
	'DcGeneral\Callbacks\Interfaces\Callbacks'                  => 'system/modules/generalDriver/DcGeneral/Callbacks/Interfaces/Callbacks.php',
	'DcGeneral\Callbacks\Callbacks'                             => 'system/modules/generalDriver/DcGeneral/Callbacks/Callbacks.php',
	'DcGeneral\Clipboard\Interfaces\Clipboard'                  => 'system/modules/generalDriver/DcGeneral/Clipboard/Interfaces/Clipboard.php',
	'DcGeneral\Clipboard\BaseClipboard'                         => 'system/modules/generalDriver/DcGeneral/Clipboard/BaseClipboard.php',
	'DcGeneral\Controller\Interfaces\Controller'                => 'system/modules/generalDriver/DcGeneral/Controller/Interfaces/Controller.php',
	'DcGeneral\Controller\Ajax'                                 => 'system/modules/generalDriver/DcGeneral/Controller/Ajax.php',
	'DcGeneral\Controller\Ajax2X'                               => 'system/modules/generalDriver/DcGeneral/Controller/Ajax2X.php',
	'DcGeneral\Controller\Ajax3X'                               => 'system/modules/generalDriver/DcGeneral/Controller/Ajax3X.php',
	'DcGeneral\Controller\Controller'                           => 'system/modules/generalDriver/DcGeneral/Controller/Controller.php',
	'DcGeneral\Contao\Dca\Container'                            => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Container.php',
	'DcGeneral\Contao\Dca\Operation'                            => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Operation.php',
	'DcGeneral\Contao\Dca\Property'                             => 'system/modules/generalDriver/DcGeneral/Contao/Dca/Property.php',
	'DcGeneral\Contao\BackendBindings'                          => 'system/modules/generalDriver/DcGeneral/Contao/BackendBindings.php',
	'DcGeneral\Contao\InputProvider'                            => 'system/modules/generalDriver/DcGeneral/Contao/InputProvider.php',
	'DcGeneral\DataDefinition\Interfaces\Container'             => 'system/modules/generalDriver/DcGeneral/DataDefinition/Interfaces/Container.php',
	'DcGeneral\DataDefinition\Interfaces\Operation'             => 'system/modules/generalDriver/DcGeneral/DataDefinition/Interfaces/Operation.php',
	'DcGeneral\DataDefinition\Interfaces\Property'              => 'system/modules/generalDriver/DcGeneral/DataDefinition/Interfaces/Property.php',
	'DcGeneral\DataDefinition\Interfaces\ParentChildCondition'  => 'system/modules/generalDriver/DcGeneral/DataDefinition/Interfaces/ParentChildCondition.php',
	'DcGeneral\Data\AbstractModel'                              => 'system/modules/generalDriver/DcGeneral/Data/AbstractModel.php',
	'DcGeneral\Data\Collection'                                 => 'system/modules/generalDriver/DcGeneral/Data/Collection.php',
	'DcGeneral\Data\Config'                                     => 'system/modules/generalDriver/DcGeneral/Data/Config.php',
	'DcGeneral\Data\DCGE'                                       => 'system/modules/generalDriver/DcGeneral/Data/DCGE.php',
	'DcGeneral\Data\Driver'                                     => 'system/modules/generalDriver/DcGeneral/Data/Driver.php',
	'DcGeneral\Data\Model'                                      => 'system/modules/generalDriver/DcGeneral/Data/Model.php',
	'DcGeneral\Data\MultiLanguageDriver'                        => 'system/modules/generalDriver/DcGeneral/Data/MultiLanguageDriver.php',
	'DcGeneral\Data\Interfaces\Collection'                      => 'system/modules/generalDriver/DcGeneral/Data/Interfaces/Collection.php',
	'DcGeneral\Data\Interfaces\Config'                          => 'system/modules/generalDriver/DcGeneral/Data/Interfaces/Config.php',
	'DcGeneral\Data\Interfaces\Driver'                          => 'system/modules/generalDriver/DcGeneral/Data/Interfaces/Driver.php',
	'DcGeneral\Data\Interfaces\Model'                           => 'system/modules/generalDriver/DcGeneral/Data/Interfaces/Model.php',
	'DcGeneral\Data\Interfaces\MultiLanguageDriver'             => 'system/modules/generalDriver/DcGeneral/Data/Interfaces/MultiLanguageDriver.php',
	'DcGeneral\DC_General'                                      => 'system/modules/generalDriver/DcGeneral/DC_General.php',
	'DcGeneral\Helper\WidgetAccessor'                           => 'system/modules/generalDriver/DcGeneral/Helper/WidgetAccessor.php',
	'DcGeneral\Interfaces\DataContainer'                        => 'system/modules/generalDriver/DcGeneral/Interfaces/DataContainer.php',
	'DcGeneral\Interfaces\Environment'                          => 'system/modules/generalDriver/DcGeneral/Interfaces/Environment.php',
	'DcGeneral\Interfaces\InputProvider'                        => 'system/modules/generalDriver/DcGeneral/Interfaces/InputProvider.php',
	'DcGeneral\Panel\AbstractElement'                           => 'system/modules/generalDriver/DcGeneral/Panel/AbstractElement.php',
	'DcGeneral\Panel\BaseContainer'                             => 'system/modules/generalDriver/DcGeneral/Panel/BaseContainer.php',
	'DcGeneral\Panel\BaseFilterElement'                         => 'system/modules/generalDriver/DcGeneral/Panel/BaseFilterElement.php',
	'DcGeneral\Panel\BaseSortElement'                           => 'system/modules/generalDriver/DcGeneral/Panel/BaseSortElement.php',
	'DcGeneral\Panel\BaseSearchElement'                         => 'system/modules/generalDriver/DcGeneral/Panel/BaseSearchElement.php',
	'DcGeneral\Panel\BaseLimitElement'                          => 'system/modules/generalDriver/DcGeneral/Panel/BaseLimitElement.php',
	'DcGeneral\Panel\BasePanel'                                 => 'system/modules/generalDriver/DcGeneral/Panel/BasePanel.php',
	'DcGeneral\Panel\Interfaces\Container'                      => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/Container.php',
	'DcGeneral\Panel\Interfaces\Element'                        => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/Element.php',
	'DcGeneral\Panel\Interfaces\FilterElement'                  => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/FilterElement.php',
	'DcGeneral\Panel\Interfaces\SortElement'                    => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/SortElement.php',
	'DcGeneral\Panel\Interfaces\SearchElement'                  => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/SearchElement.php',
	'DcGeneral\Panel\Interfaces\LimitElement'                   => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/LimitElement.php',
	'DcGeneral\Panel\Interfaces\Panel'                          => 'system/modules/generalDriver/DcGeneral/Panel/Interfaces/Panel.php',
	'DcGeneral\View\Interfaces\View'                            => 'system/modules/generalDriver/DcGeneral/View/Interfaces/View.php',
	'DcGeneral\View\View'                                       => 'system/modules/generalDriver/DcGeneral/View/View.php',

	// FIXME: we can not deprecate this class as the only way for Contao is to load the class from root namespace.
	'DC_General'                                                => 'system/modules/generalDriver/DC_General.php',

	// Backwards compatibility layer from here on. DEPRECATED only, DO NOT USE.
	'DCGE'                                          => 'system/modules/generalDriver/DCGE.php',
	'GeneralAjax'                                   => 'system/modules/generalDriver/GeneralAjax.php',
	'GeneralAjax2X'                                 => 'system/modules/generalDriver/GeneralAjax2X.php',
	'GeneralAjax3X'                                 => 'system/modules/generalDriver/GeneralAjax3X.php',
	'GeneralCallbackDefault'                        => 'system/modules/generalDriver/GeneralCallbackDefault.php',
	'GeneralCollectionDefault'                      => 'system/modules/generalDriver/GeneralCollectionDefault.php',
	'GeneralControllerDefault'                      => 'system/modules/generalDriver/GeneralControllerDefault.php',
	'GeneralDataConfigDefault'                      => 'system/modules/generalDriver/GeneralDataConfigDefault.php',
	'GeneralDataDefault'                            => 'system/modules/generalDriver/GeneralDataDefault.php',
	'GeneralDataMultiLanguageDefault'               => 'system/modules/generalDriver/GeneralDataMultiLanguageDefault.php',
	'GeneralDataTableRowsAsRecords'                 => 'system/modules/generalDriver/GeneralDataTableRowsAsRecords.php',
	'GeneralModelDefault'                           => 'system/modules/generalDriver/GeneralModelDefault.php',
	'GeneralViewDefault'                            => 'system/modules/generalDriver/GeneralViewDefault.php',
	'InterfaceGeneralCallback'                      => 'system/modules/generalDriver/InterfaceGeneralCallback.php',
	'InterfaceGeneralCollection'                    => 'system/modules/generalDriver/InterfaceGeneralCollection.php',
	'InterfaceGeneralController'                    => 'system/modules/generalDriver/InterfaceGeneralController.php',
	'InterfaceGeneralData'                          => 'system/modules/generalDriver/InterfaceGeneralData.php',
	'InterfaceGeneralDataConfig'                    => 'system/modules/generalDriver/InterfaceGeneralDataConfig.php',
	'InterfaceGeneralDataMultiLanguage'             => 'system/modules/generalDriver/InterfaceGeneralDataMultiLanguage.php',
	'InterfaceGeneralModel'                         => 'system/modules/generalDriver/InterfaceGeneralModel.php',
	'InterfaceGeneralView'                          => 'system/modules/generalDriver/InterfaceGeneralView.php',
	'WidgetAccessor'                                => 'system/modules/generalDriver/WidgetAccessor.php',

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
	'dcbe_general_parentView'       => 'system/modules/generalDriver/templates',
	'dcbe_general_show'             => 'system/modules/generalDriver/templates',
	'dcbe_general_submit'           => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview'         => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_child'   => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_entry'   => 'system/modules/generalDriver/templates',
	'dcbe_general_breadcrumb'       => 'system/modules/generalDriver/templates',
));
