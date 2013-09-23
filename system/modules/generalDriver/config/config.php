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

// For the moment, we add our autoloader for non composerized Contao 2.X compat.
if (version_compare(VERSION, '3.0', '<'))
{
	function dcGeneral_autoload($className)
	{
		$className = ltrim($className, '\\');
		$fileName  = '';

		if ($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= $className . '.php';

		foreach (array(
			dirname(__DIR__),
			dirname(__DIR__) . DIRECTORY_SEPARATOR . 'deprecated'
		) as $baseDir)
		{
			if (file_exists($baseDir . DIRECTORY_SEPARATOR . $fileName))
			{
				require $baseDir . DIRECTORY_SEPARATOR . $fileName;

				// Tell the Contao 2.X auto loader cache that the class is available.
				if (class_exists('Cache'))
				{
					\Cache::getInstance()->{'classFileExists-' . $className} = true;
				}
				\FileCache::getInstance('classes')->$className = true;

				return true;
			}
		}

		return null;
	}

	spl_autoload_unregister('__autoload');
	spl_autoload_register('dcGeneral_autoload', true, false);
	spl_autoload_register('__autoload');
}
