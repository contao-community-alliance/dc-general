<?php
/**
 * PHP version 5
 *
 * @package    dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

// Register the templates.
TemplateLoader::addFiles(
    array
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
        'dcbe_general_clipboard'        => 'system/modules/dc-general/templates',
        'dcbe_general_grouping'         => 'system/modules/dc-general/templates',
        'widget_filetree'               => 'system/modules/dc-general/templates',
        'widget_filetree_order'         => 'system/modules/dc-general/templates',
        'widget_treepicker'             => 'system/modules/dc-general/templates',
        'widget_treepicker_popup'       => 'system/modules/dc-general/templates',
        'widget_treepicker_entry'       => 'system/modules/dc-general/templates',
        'widget_treepicker_child'       => 'system/modules/dc-general/templates',
    )
);
