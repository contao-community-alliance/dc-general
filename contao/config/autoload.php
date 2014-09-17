<?php
/**
 * PHP version 5
 * @package    dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // FIXME: we can not deprecate this class as the only way for Contao is to load the class from root namespace.
    'DC_General'                                                => 'system/modules/dc-general/DC_General.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'dcbe_general_edit'             => 'system/modules/dc-general/templates',
    'dcbe_general_field'            => 'system/modules/dc-general/templates',
    'dcbe_general_language_panel'   => 'system/modules/dc-general/templates',
    'dcbe_general_listView'         => 'system/modules/dc-general/templates',
    'dcbe_general_listView_sorting' => 'system/modules/dc-general/templates',
    'dcbe_general_panel'            => 'system/modules/dc-general/templates',
    'dcbe_general_panel_filter'     => 'system/modules/dc-general/templates',
    'dcbe_general_panel_limit'      => 'system/modules/dc-general/templates',
    'dcbe_general_panel_search'     => 'system/modules/dc-general/templates',
    'dcbe_general_panel_sort'       => 'system/modules/dc-general/templates',
    'dcbe_general_panel_submit'     => 'system/modules/dc-general/templates',
    'dcbe_general_parentView'       => 'system/modules/dc-general/templates',
    'dcbe_general_show'             => 'system/modules/dc-general/templates',
    'dcbe_general_treeview'         => 'system/modules/dc-general/templates',
    'dcbe_general_treeview_child'   => 'system/modules/dc-general/templates',
    'dcbe_general_treeview_entry'   => 'system/modules/dc-general/templates',
    'dcbe_general_breadcrumb'       => 'system/modules/dc-general/templates',
    'dcbe_general_grouping'         => 'system/modules/dc-general/templates',
    'widget_treepicker'             => 'system/modules/dc-general/templates',
    'widget_treepicker_popup'       => 'system/modules/dc-general/templates',
    'widget_treepicker_entry'       => 'system/modules/dc-general/templates',
    'widget_treepicker_child'       => 'system/modules/dc-general/templates',
));
