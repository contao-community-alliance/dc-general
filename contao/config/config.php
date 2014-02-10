<?php

/**
 * PHP version 5
 * @package    dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

/**
 * JS
 */
if (TL_MODE == 'BE')
{
	$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dc-general/html/js/generalDriver_src.js';
	$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dc-general/html/js/vanillaGeneral.js';
}

// For the moment, we add our auto loader at the end for non composerized Contao 2.X compatibility.
if (version_compare(VERSION, '3.0', '<') && !class_exists('ContaoCommunityAlliance\Contao\Composer\ClassLoader', false))
{
	$baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'DcGeneral' . DIRECTORY_SEPARATOR . 'Contao' . DIRECTORY_SEPARATOR . 'Compatibility' . DIRECTORY_SEPARATOR;
	// Fake the Contao 3 class loading.
	require_once  $baseDir . 'ClassLoader.php';
	if (!class_exists('ClassLoader', false))
	{
		class_alias('DcGeneral\Contao\Compatibility\ClassLoader', 'ClassLoader');
	}
	require_once $baseDir . 'TemplateLoader.php';
	if (!class_exists('TemplateLoader', false))
	{
		class_alias('DcGeneral\Contao\Compatibility\TemplateLoader', 'TemplateLoader');
	}
	DcGeneral\Contao\Compatibility\ClassLoader::scanAndRegister();
}

// Attach ourselves to the DIC.
$GLOBALS['TL_EVENT_SUBSCRIBERS'][] = 'DcGeneral\Contao\Event\Subscriber';

// TODO: defining the event handlers like this is pretty ugly, we should really make this better.
$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\BuildDataDefinitionEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::process',
	\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::PRIORITY
);
$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\BuildDataDefinitionEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder::process',
	\DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder::PRIORITY
);

$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Populator\DataProviderPopulator::process',
	\DcGeneral\Contao\Dca\Populator\DataProviderPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator::process',
	\DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Populator\BackendViewPopulator::process',
	\DcGeneral\Contao\Dca\Populator\BackendViewPopulator::PRIORITY
);

$GLOBALS['TL_EVENTS'][\DcGeneral\Factory\Event\PopulateEnvironmentEvent::NAME][] = array(
	'DcGeneral\Contao\Dca\Populator\HardCodedPopulator::process',
	\DcGeneral\Contao\Dca\Populator\HardCodedPopulator::PRIORITY
);
