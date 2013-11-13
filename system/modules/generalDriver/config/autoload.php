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
// TODO temporary remove all classes, we need to update them for non-composer support

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
