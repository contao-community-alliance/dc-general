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

namespace DcGeneral\Contao\BackendBindingInternal;

/**
 * Class ContaoTwoEleven.
 *
 * Contao 2.11 bindings.
 *
 * @package DcGeneral\Contao\BackendBindingInternal
 */
class ContaoTwoEleven extends \Backend
{
	/**
	 * The singleton instance.
	 *
	 * @var ContaoThree
	 */
	protected static $objInstance;

	/**
	 * Create a new instance. This is not directly creatable, use the getInstance() method to retrieve the singleton.
	 */
	protected function __construct()
	{
		self::$objInstance = $this;
		parent::__construct();
	}

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return ContaoThree
	 */
	public static function getInstance()
	{
		if (!self::$objInstance)
		{
			new self;
		}

		return self::$objInstance;
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
	public function log($strText, $strFunction, $strCategory)
	{
		parent::log($strText, $strFunction, $strCategory);
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
	public function redirect($strLocation, $intStatus)
	{
		parent::redirect($strLocation, $intStatus);
	}

	/**
	 * Reload the current page.
	 *
	 * NOTE: This method exits the script.
	 *
	 * @return void
	 */
	public function reload()
	{
		parent::reload();
	}

	/**
	 * Add a request string to the current URI string.
	 *
	 * @param string $strRequest The parameters to add to the current URL separated by &.
	 *
	 * @return string
	 */
	public function addToUrl($strRequest)
	{
		return parent::addToUrl($strRequest);
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
	public function loadLanguageFile($strName, $strLanguage = false, $blnNoCache = false)
	{
		parent::loadLanguageFile($strName, $strLanguage, $blnNoCache);
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
	public function generateImage($src, $alt = '', $attributes = '')
	{
		return parent::generateImage($src, $alt, $attributes);
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
	public function getImage($image, $width, $height, $mode = '', $target = null, $force = false)
	{
		return parent::getImage($image, $width, $height, $mode, $target, $force);
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
	public function getReferer($blnEncodeAmpersands = false, $strTable = null)
	{
		return parent::getReferer($blnEncodeAmpersands, $strTable);
	}

	/**
	 * Load a set of DCA files.
	 *
	 * @param string  $strName    The table name.
	 *
	 * @param boolean $blnNoCache If true, the cache will be bypassed.
	 *
	 * @return void
	 */
	public function loadDataContainer($strName, $blnNoCache = false)
	{
		parent::loadDataContainer($strName, $blnNoCache);
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
	public function parseDate($strFormat, $intTimestamp = null)
	{
		return parent::parseDate($strFormat, $intTimestamp);
	}
}
