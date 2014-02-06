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
	// TODO temporarily remove all classes, we need to update them for non-composer support

	// FIXME: we can not deprecate this class as the only way for Contao is to load the class from root namespace.
	'DC_General'                                                => 'system/modules/generalDriver/DC_General.php',

	// Backwards compatibility layer from here on. DEPRECATED only, DO NOT USE.
	'GeneralDataMultiLanguageDefault'                          => 'system/modules/generalDriver/deprecated/GeneralDataMultiLanguageDefault.php',
	'GeneralControllerDefault'                                 => 'system/modules/generalDriver/deprecated/GeneralControllerDefault.php',
	'AbstractGeneralModel'                                     => 'system/modules/generalDriver/deprecated/AbstractGeneralModel.php',
	'GeneralAjax2X'                                            => 'system/modules/generalDriver/deprecated/GeneralAjax2X.php',
	'GeneralAjax'                                              => 'system/modules/generalDriver/deprecated/GeneralAjax.php',
	'InterfaceGeneralModel'                                    => 'system/modules/generalDriver/deprecated/InterfaceGeneralModel.php',
	'GeneralCollectionDefault'                                 => 'system/modules/generalDriver/deprecated/GeneralCollectionDefault.php',
	'InterfaceGeneralController'                               => 'system/modules/generalDriver/deprecated/InterfaceGeneralController.php',
	'GeneralDataDefault'                                       => 'system/modules/generalDriver/deprecated/GeneralDataDefault.php',
	'InterfaceGeneralCollection'                               => 'system/modules/generalDriver/deprecated/InterfaceGeneralCollection.php',
	'InterfaceGeneralDataMultiLanguage'                        => 'system/modules/generalDriver/deprecated/InterfaceGeneralDataMultiLanguage.php',
	'GeneralDataTableRowsAsRecords'                            => 'system/modules/generalDriver/deprecated/GeneralDataTableRowsAsRecords.php',
	'GeneralDataConfigDefault'                                 => 'system/modules/generalDriver/deprecated/GeneralDataConfigDefault.php',
	'GeneralAjax3X'                                            => 'system/modules/generalDriver/deprecated/GeneralAjax3X.php',
	'GeneralViewDefault'                                       => 'system/modules/generalDriver/deprecated/GeneralViewDefault.php',
	'WidgetAccessor'                                           => 'system/modules/generalDriver/deprecated/WidgetAccessor.php',
	'InterfaceGeneralDataConfig'                               => 'system/modules/generalDriver/deprecated/InterfaceGeneralDataConfig.php',
	'DCGE'                                                     => 'system/modules/generalDriver/deprecated/DCGE.php',
	'InterfaceGeneralData'                                     => 'system/modules/generalDriver/deprecated/InterfaceGeneralData.php',
	'InterfaceGeneralView'                                     => 'system/modules/generalDriver/deprecated/InterfaceGeneralView.php',
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
	'dcbe_general_grouping'         => 'system/modules/generalDriver/templates',
));
