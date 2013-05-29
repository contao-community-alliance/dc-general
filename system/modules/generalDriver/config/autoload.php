<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package GeneralDriver
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'DC_General'                        => 'system/modules/generalDriver/DC_General.php',
	'DCGE'                              => 'system/modules/generalDriver/DCGE.php',
	'GeneralAjax'                       => 'system/modules/generalDriver/GeneralAjax.php',
	'GeneralCallbackDefault'            => 'system/modules/generalDriver/GeneralCallbackDefault.php',
	'GeneralCollectionDefault'          => 'system/modules/generalDriver/GeneralCollectionDefault.php',
	'GeneralControllerDefault'          => 'system/modules/generalDriver/GeneralControllerDefault.php',
	'GeneralDataConfigDefault'          => 'system/modules/generalDriver/GeneralDataConfigDefault.php',
	'GeneralDataDefault'                => 'system/modules/generalDriver/GeneralDataDefault.php',
	'GeneralDataMultiLanguageDefault'   => 'system/modules/generalDriver/GeneralDataMultiLanguageDefault.php',
	'GeneralDataTableRowsAsRecords'     => 'system/modules/generalDriver/GeneralDataTableRowsAsRecords.php',
	'GeneralModelDefault'               => 'system/modules/generalDriver/GeneralModelDefault.php',
	'GeneralViewDefault'                => 'system/modules/generalDriver/GeneralViewDefault.php',
	'InterfaceGeneralCallback'          => 'system/modules/generalDriver/InterfaceGeneralCallback.php',
	'InterfaceGeneralCollection'        => 'system/modules/generalDriver/InterfaceGeneralCollection.php',
	'InterfaceGeneralController'        => 'system/modules/generalDriver/InterfaceGeneralController.php',
	'InterfaceGeneralData'              => 'system/modules/generalDriver/InterfaceGeneralData.php',
	'InterfaceGeneralDataConfig'        => 'system/modules/generalDriver/InterfaceGeneralDataConfig.php',
	'InterfaceGeneralDataMultiLanguage' => 'system/modules/generalDriver/InterfaceGeneralDataMultiLanguage.php',
	'InterfaceGeneralModel'             => 'system/modules/generalDriver/InterfaceGeneralModel.php',
	'InterfaceGeneralView'              => 'system/modules/generalDriver/InterfaceGeneralView.php',
	'WidgetAccessor'                    => 'system/modules/generalDriver/WidgetAccessor.php',
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
	'dcbe_general_parentView'       => 'system/modules/generalDriver/templates',
	'dcbe_general_show'             => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview'         => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_child'   => 'system/modules/generalDriver/templates',
	'dcbe_general_treeview_entry'   => 'system/modules/generalDriver/templates',
	'dcbe_general_breadcrumb'       => 'system/modules/generalDriver/templates',
));
