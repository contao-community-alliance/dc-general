<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Contao;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;

/**
 * Class BackendBindings.
 *
 * This class abstracts the Contao backend methods used within DcGeneral over all Contao versions.
 *
 * This is needed to limit the amount of version_compare() calls to an absolute minimum.
 *
 * @package DcGeneral\Contao
 *
 * @deprecated Use events-contao-bindings package.
 */
class BackendBindings
{
	protected static function dispatch($eventName, $event)
	{
		return $GLOBALS['container']['event-dispatcher']->dispatch($eventName, $event);
	}

	/**
	 * Log a message to the contao system log.
	 *
	 * @param string $strText     The message text to add to the log.
	 *
	 * @param string $strFunction The method/function the message originates from.
	 *
	 * @param string $strCategory The category under which the message shall be logged.
	 *
	 * @return void
	 */
	public static function log($strText, $strFunction, $strCategory)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		self::dispatch(
			ContaoEvents::SYSTEM_LOG,
			new LogEvent(
				$strText,
				$strFunction,
				$strCategory
			)
		);
	}

	/**
	 * Redirect the browser to a new location.
	 *
	 * NOTE: This method exits the script.
	 *
	 * @param string $strLocation The new URI to which the browser shall get redirected to.
	 *
	 * @param int    $intStatus   The HTTP status code to use. 301, 302, 303, 307. Defaults to 303.
	 *
	 * @return void
	 */
	public static function redirect($strLocation, $intStatus = 303)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		self::dispatch(
			ContaoEvents::CONTROLLER_REDIRECT,
			new RedirectEvent(
				$strLocation,
				$intStatus
			)
		);
	}

	/**
	 * Reload the current page.
	 *
	 * NOTE: This method exits the script.
	 *
	 * @return void
	 */
	public static function reload()
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		self::dispatch(
			ContaoEvents::CONTROLLER_RELOAD,
			new ReloadEvent(
			)
		);
	}

	/**
	 * Add a request string to the current URI string.
	 *
	 * @param string $strRequest The parameters to add to the current URL separated by &.
	 *
	 * @return string
	 */
	public static function addToUrl($strRequest)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		return self::dispatch(
			ContaoEvents::BACKEND_ADD_TO_URL,
			new AddToUrlEvent(
				$strRequest
			)
		)->getUrl();
	}

	/**
	 * Load a set of language files.
	 *
	 * @param string  $strName     The table name.
	 *
	 * @param boolean $strLanguage An optional language code.
	 *
	 * @param boolean $blnNoCache  If true, the cache will be bypassed.
	 *
	 * @return void
	 *
	 * @throws \Exception In case a language does not exist.
	 */
	public static function loadLanguageFile($strName, $strLanguage = null, $blnNoCache = false)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		self::dispatch(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent(
				$strName,
				$strLanguage,
				$blnNoCache
			)
		);
	}

	/**
	 * Resize an image and store the resized version in the assets/images folder.
	 *
	 * @param string  $image  The image path.
	 * @param integer $width  The target width.
	 * @param integer $height The target height.
	 * @param string  $mode   The resize mode.
	 * @param string  $target An optional target path.
	 * @param boolean $force  Override existing target images.
	 *
	 * @return string|null The path of the resized image or null.
	 */
	public static function getImage($image, $width, $height, $mode = '', $target = null, $force = false)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		return self::dispatch(
			ContaoEvents::IMAGE_RESIZE,
			new ResizeImageEvent(
				$image,
				$width,
				$height,
				$mode,
				$target,
				$force
			)
		)->getResultImage();
	}

	/**
	 * Generate an image tag and return it as string.
	 *
	 * @param string $src        The image path.
	 * @param string $alt        An optional alt attribute.
	 * @param string $attributes A string of other attributes.
	 *
	 * @return string The image HTML tag.
	 */
	public static function generateImage($src, $alt = '', $attributes = '')
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		return self::dispatch(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent(
				$src,
				$alt,
				$attributes
			)
		)->getHtml();
	}

	/**
	 * Return the current referer URL and optionally encode ampersands.
	 *
	 * @param boolean $blnEncodeAmpersands If true, ampersands will be encoded.
	 *
	 * @param string  $strTable            An optional table name.
	 *
	 * @return string The referer URL
	 */
	public static function getReferer($blnEncodeAmpersands = false, $strTable = null)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		return self::dispatch(
			ContaoEvents::SYSTEM_GET_REFERRER,
			new GetReferrerEvent(
				$blnEncodeAmpersands,
				$strTable
			)
		)->getReferrerUrl();
	}

	/**
	 * Load a set of DCA files.
	 *
	 * @param string  $strTable   The table name.
	 *
	 * @param boolean $blnNoCache If true, the cache will be bypassed.
	 *
	 * @return void
	 */
	public static function loadDataContainer($strTable, $blnNoCache = false)
	{
		trigger_error(__FILE__ . '::' . __METHOD__ . ' deprecated, use events-contao-bindings.');
		self::dispatch(
			ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER,
			new LoadDataContainerEvent(
				$strTable,
				$blnNoCache
			)
		);
	}

	/**
	 * Parse a date format string and translate textual representations.
	 *
	 * @param string  $strFormat    The date format string.
	 *
	 * @param integer $intTimestamp An optional timestamp.
	 *
	 * @return string The textual representation of the date.
	 */
	public static function parseDate($strFormat, $intTimestamp = null)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Date::parse($strFormat, $intTimestamp);
		}

		return BackendBindingInternal::getInstance()->parseDate($strFormat, $intTimestamp);
	}

	/**
	 * Shorten a HTML string to a certain number of characters.
	 *
	 * Shortens a string to a given number of characters preserving words
	 * (therefore it might be a bit shorter or longer than the number of
	 * characters specified). Preserves allowed tags.
	 *
	 * @param string  $strString        The Html string to cut.
	 *
	 * @param integer $intNumberOfChars The amount of chars to preserve.
	 *
	 * @return string
	 */
	public static function substrHtml($strString, $intNumberOfChars)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \String::substrHtml($strString, $intNumberOfChars);
		}

		return \String::getInstance()->substrHtml($strString, $intNumberOfChars);
	}
}
