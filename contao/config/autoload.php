<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

// Register the templates.
TemplateLoader::addFiles(
    array
    (
        'dcbe_general_common_list'      => 'system/modules/dc-general/templates',
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
