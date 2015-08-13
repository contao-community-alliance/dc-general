<?php

/**
 * PHP version 5
 *
 * @package    dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

/**
 * JS
 */
if (TL_MODE == 'BE') {
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dc-general/html/js/generalDriver_src.js';
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dc-general/html/js/vanillaGeneral.js';
}

$GLOBALS['BE_FFL']['DcGeneralTreePicker'] =
    'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker';

$GLOBALS['TL_HOOKS']['executePostActions'][] =
    array('ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker', 'updateAjax');
