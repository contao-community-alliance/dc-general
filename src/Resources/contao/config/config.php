<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

// JS
$isBackend = (bool) System::getContainer()
    ->get('contao.routing.scope_matcher')
    ?->isBackendRequest(
        System::getContainer()->get('request_stack')?->getCurrentRequest() ?? Request::create('')
    );

if ($isBackend) {
    // The ajax helpers are the shared base of the scripts below, so they have to be loaded first.
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.generalAjax']        = '/bundles/ccadcgeneral/js/generalAjax.js';
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.generalDriver']      = '/bundles/ccadcgeneral/js/generalDriver.js';
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.generalBase']        = '/bundles/ccadcgeneral/js/generalBase.js';
    $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.sortableOrderField'] = '/bundles/ccadcgeneral/js/sortableOrderField.js';
}

$GLOBALS['BE_FFL']['DcGeneralTreePicker'] = TreePicker::class;

$GLOBALS['TL_HOOKS']['executePostActions'] = \array_merge(
    (array) ($GLOBALS['TL_HOOKS']['executePostActions'] ?? []),
    [
        [TreePicker::class, 'updateAjax']
    ]
);
