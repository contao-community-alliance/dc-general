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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
