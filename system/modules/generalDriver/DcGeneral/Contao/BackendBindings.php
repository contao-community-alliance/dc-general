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

if (version_compare(VERSION, '3.0', '<'))
{
	class BackendBindingInternal extends \Backend
	{
		protected static $objInstance;

		protected function __construct()
		{
			self::$objInstance = $this;
			parent::__construct();
		}

		public static function getInstance()
		{
			if (!self::$objInstance)
			{
				new self;
			}

			return self::$objInstance;
		}

		public function log($strText, $strFunction, $strCategory)
		{
			parent::log($strText, $strFunction, $strCategory);
		}

		public function redirect($strLocation, $intStatus)
		{
			parent::redirect($strLocation, $intStatus);
		}

		public function addToUrl($strRequest)
		{
			return parent::addToUrl($strRequest);
		}

		public function loadLanguageFile($strName, $strLanguage = false, $blnNoCache = false)
		{
			parent::loadLanguageFile($strName, $strLanguage, $blnNoCache);
		}

		public function generateImage($src, $alt = '', $attributes = '')
		{
			return parent::generateImage($src, $alt, $attributes);
		}

		public function getImage($image, $width, $height, $mode = '', $target = NULL, $force = false)
		{
			return parent::getImage($image, $width, $height, $mode, $target, $force);
		}

		public function getReferer($blnEncodeAmpersands=false, $strTable=null)
		{
			return parent::getReferer($blnEncodeAmpersands, $strTable);
		}

		public function loadDataContainer($strName, $blnNoCache=false)
		{
			parent::loadDataContainer($strName, $blnNoCache);
		}

		public function parseDate($strFormat, $intTimestamp=null)
		{
			return parent::parseDate($strFormat, $intTimestamp);
		}
	}
}
else
{
	class BackendBindingInternal extends \Backend
	{
		protected static $objInstance;

		protected function __construct()
		{
			self::$objInstance = $this;
			parent::__construct();
		}

		public static function getInstance()
		{
			if (!self::$objInstance)
			{
				new self;
			}

			return self::$objInstance;
		}

		public function loadDataContainer($strName, $blnNoCache=false)
		{
			parent::loadDataContainer($strName, $blnNoCache);
		}
	}
}

class BackendBindings
{
	public static function log($strText, $strFunction, $strCategory)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			\Backend::log($strText, $strFunction, $strCategory);
		}
		else
		{
			BackendBindingInternal::getInstance()->log($strText, $strFunction, $strCategory);
		}
	}

	public static function redirect($strLocation, $intStatus = 303)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			\Backend::redirect($strLocation, $intStatus);
		}
		else
		{
			BackendBindingInternal::getInstance()->redirect($strLocation, $intStatus);
		}
	}

	public static function addToUrl($strRequest)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Backend::addToUrl($strRequest);
		}
		else
		{
			return BackendBindingInternal::getInstance()->addToUrl($strRequest);
		}
	}

	public static function loadLanguageFile($strName, $strLanguage=null, $blnNoCache=false)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			\Backend::loadLanguageFile($strName, $strLanguage, $blnNoCache);
		}
		else
		{
			BackendBindingInternal::getInstance()->loadLanguageFile($strName, $strLanguage, $blnNoCache);
		}
	}

	public static function getImage($image, $width, $height, $mode='', $target=null, $force=false)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Image::get($image, $width, $height, $mode, $target, $force);
		}
		else{
			return BackendBindingInternal::getInstance()->getImage($image, $width, $height, $mode, $target, $force);
		}
	}

	public static function generateImage($src, $alt='', $attributes='')
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Image::getHtml($src, $alt, $attributes);
		}
		else{
			return BackendBindingInternal::getInstance()->generateImage($src, $alt, $attributes);
		}
	}

	public static function getReferer($blnEncodeAmpersands=false, $strTable=null)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Backend::getReferer($blnEncodeAmpersands, $strTable);
		}
		else{
			return BackendBindingInternal::getInstance()->getReferer($blnEncodeAmpersands, $strTable);
		}
	}

	public static function loadDataContainer($strTable, $blnNoCache=false)
	{
		BackendBindingInternal::getInstance()->loadDataContainer($strTable, $blnNoCache);
	}

	public static function parseDate($strFormat, $intTimestamp=null)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \Date::parse($strFormat, $intTimestamp);
		}
		else{
			return BackendBindingInternal::getInstance()->parseDate($strFormat, $intTimestamp);
		}
	}

	public static function substrHtml($strString, $intNumberOfChars)
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			return \String::substrHtml($strString, $intNumberOfChars);
		}
		else
		{
			return \String::getInstance()->substrHtml($strString, $intNumberOfChars);
		}
	}
}
