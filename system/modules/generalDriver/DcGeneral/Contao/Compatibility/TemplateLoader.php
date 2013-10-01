<?php

namespace MetaModels\Compatibility;

class TemplateLoader
{
	/**
	 * No op.
	 *
	 * @param string $name The template name
	 * @param string $file The path to the template folder
	 */
	public static function addFile($name, $file)
	{
		// No op.
	}


	/**
	 * No op.
	 *
	 * @param array $files An array of files
	 */
	public static function addFiles($files)
	{
		// No op.
	}

	/**
	 * No op
	 *
	 * @throws \Exception
	 */
	public static function getFiles()
	{
		throw new \Exception('TemplateLoader is a non op compatibility class by MetaModels.');
	}

	/**
	 * No op
	 *
	 * @param string $prefix The prefix (e.g. "moo_")
	 *
	 * @throws \Exception
	 */
	public static function getPrefixedFiles($prefix)
	{
		throw new \Exception('TemplateLoader is a non op compatibility class by MetaModels.');
	}

	/**
	 * No op.
	 *
	 * @param string $template The template name
	 * @param string $format   The output format (e.g. "html5")
	 * @param string $custom   The custom templates folder (defaults to "templates")
	 *
	 * @throws \Exception If $template does not exist
	 */
	public static function getPath($template, $format, $custom='templates')
	{
		throw new \Exception('TemplateLoader is a non op compatibility class by MetaModels.');
	}
}
