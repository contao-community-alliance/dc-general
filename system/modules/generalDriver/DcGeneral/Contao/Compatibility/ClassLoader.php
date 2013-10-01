<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Compatibility;

/**
 * Class ClassLoader
 *
 * This class simply exists to provide the Contao 3 namespace based auto loading in Contao 2.11.
 * It is heavily based upon code by Leo Feyer and only temporary.
 *
 * @package MetaModels\Compatibility
 */
class ClassLoader
{
	/**
	 * Known namespaces
	 * @var array
	 */
	protected static $namespaces = array
	(
		'Contao'
	);

	/**
	 * Known classes
	 * @var array
	 */
	protected static $classes = array();

	/**
	 * Add a new namespace
	 *
	 * @param string $name The namespace name
	 */
	public static function addNamespace($name)
	{
		if (in_array($name, self::$namespaces))
		{
			return;
		}

		array_unshift(self::$namespaces, $name);
	}


	/**
	 * Add multiple new namespaces
	 *
	 * @param array $names An array of namespace names
	 */
	public static function addNamespaces($names)
	{
		foreach ($names as $name)
		{
			self::addNamespace($name);
		}
	}


	/**
	 * Return the namespaces as array
	 *
	 * @return array An array of all namespaces
	 */
	public static function getNamespaces()
	{
		return self::$namespaces;
	}


	/**
	 * Add a new class with its file path
	 *
	 * @param string $class The class name
	 * @param string $file  The path to the class file
	 */
	public static function addClass($class, $file)
	{
		self::$classes[$class] = $file;

		// Tell the Contao 2.X auto loader cache that the class is available.
		if (class_exists('Cache'))
		{
			\Cache::getInstance()->{'classFileExists-' . $class} = true;
		}
		\FileCache::getInstance('classes')->$class = true;
		\FileCache::getInstance('autoload')->$class = $file;
	}


	/**
	 * Add multiple new classes with their file paths
	 *
	 * @param array $classes An array of classes
	 */
	public static function addClasses($classes)
	{
		foreach ($classes as $class=>$file)
		{
			self::addClass($class, $file);
		}
	}


	/**
	 * Return the classes as array.
	 *
	 * @return array An array of all classes
	 */
	public static function getClasses()
	{
		return self::$classes;
	}


	/**
	 * Autoload a class and create an alias in the global namespace
	 *
	 * To preserve backwards compatibility with Contao 2 extensions, all core
	 * classes will be aliased into the global namespace.
	 *
	 * @param string $class The class name
	 */
	public static function load($class)
	{
		if (class_exists($class, false) || interface_exists($class, false))
		{
			return;
		}

		// The class file is set in the mapper
		if (isset(self::$classes[$class]))
		{
			if ($GLOBALS['TL_CONFIG']['debugMode'])
			{
				$GLOBALS['TL_DEBUG']['classes_set'][] = $class;
			}

			include TL_ROOT . '/' . self::$classes[$class];
		}

		// Find the class in the registered namespaces
		elseif (($namespaced = self::findClass($class)) != false)
		{
			if ($GLOBALS['TL_CONFIG']['debugMode'])
			{
				$GLOBALS['TL_DEBUG']['classes_aliased'][] = $class . ' <span style="color:#999">(' . $namespaced . ')</span>';
			}

			include TL_ROOT . '/' . self::$classes[$namespaced];
			class_alias($namespaced, $class);
		}

		// Pass the request to other autoloaders (e.g. Swift)
	}


	/**
	 * Search the namespaces for a matching entry
	 *
	 * @param string $class The class name
	 *
	 * @return string The full path including the namespace
	 */
	protected static function findClass($class)
	{
		foreach (self::$namespaces as $namespace)
		{
			if (isset(self::$classes[$namespace . '\\' . $class]))
			{
				return $namespace . '\\' . $class;
			}
		}

		return '';
	}


	/**
	 * Register the autoloader
	 */
	public static function register()
	{
		spl_autoload_unregister('__autoload');
		spl_autoload_register('ClassLoader::load', true, false);
		spl_autoload_register('__autoload');
	}


	/**
	 * Dummy method.
	 */
	public static function scanAndRegister()
	{
		$modules = TL_ROOT . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'modules';

		foreach (scan($modules) as $module)
		{
			$file = $modules . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload.php';
			if (file_exists($file))
			{
				require_once $file;
			}
		}

		self::register();
	}
}
