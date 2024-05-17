<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\FileTree;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;

// JS
if (defined('TL_MODE') && TL_MODE === 'BE') {
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.generalDriver_src'] = 'bundles/ccadcgeneral/js/generalDriver_src.js';
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.vanillaGeneral']    = 'bundles/ccadcgeneral/js/vanillaGeneral.js';
}

$GLOBALS['BE_FFL']['DcGeneralTreePicker'] = TreePicker::class;

$GLOBALS['TL_HOOKS']['executePostActions'] = \array_merge(
    (array) ($GLOBALS['TL_HOOKS']['executePostActions'] ?? []),
    [
        [TreePicker::class, 'updateAjax'],
        [FileTree::class, 'updateAjax']
    ]
);
