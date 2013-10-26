<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * The ABI version of the DcGeneral extension in use.
 */
define('DCGENERAL_VERSION', '1.0.0-dev');

/**
 * JS
 */
if(TL_MODE == 'BE')
{
	$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/generalDriver/html/js/generalDriver.js';
}

// For the moment, we add our auto loader at the end for non composerized Contao 2.X compatibility.
if (version_compare(VERSION, '3.0', '<'))
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
$GLOBALS['TL_EVENT_SUBSCRIBERS'][] = 'DcGeneral\Events\Subscriber';

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
	'DcGeneral\Contao\Dca\Populator\HardCodedPopulator::process',
	\DcGeneral\Contao\Dca\Populator\HardCodedPopulator::PRIORITY
);
