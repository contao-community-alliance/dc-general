<?php

/**
 * PHP version 5
 * @package	   generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

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
	class_alias('DcGeneral\Contao\Compatibility\ClassLoader', 'ClassLoader');
	require_once $baseDir . 'TemplateLoader.php';
	class_alias('DcGeneral\Contao\Compatibility\TemplateLoader', 'TemplateLoader');
	DcGeneral\Contao\Compatibility\ClassLoader::scanAndRegister();
}

