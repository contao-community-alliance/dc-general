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

// Attach ourselves to the DIC.
$GLOBALS['TL_EVENT_SUBSCRIBERS'][] = 'ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber';

// TODO: defining the event handlers like this is pretty ugly, we should really make this better.
$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::PRIORITY
);
$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder::PRIORITY
);

$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\DataProviderPopulator::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\DataProviderPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\BackendViewPopulator::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\BackendViewPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\HardCodedPopulator::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\HardCodedPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
    'ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\PickerCompatPopulator::process',
    \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\PickerCompatPopulator::PRIORITY
);
