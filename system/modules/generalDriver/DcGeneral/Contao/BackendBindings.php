<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
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

		public function addToUrl($strRequest)
		{
			return parent::addToUrl($strRequest);
		}

		public function loadLanguageFile($strName, $strLanguage = false, $blnNoCache = false)
		{
			parent::loadLanguageFile($strName, $strLanguage, $blnNoCache);
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
			return parent::getReferer($strName, $blnNoCache);
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
			return parent::getReferer($strName, $blnNoCache);
		}
	}
}

class BackendBindings
{
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
		return BackendBindingInternal::getInstance()->loadDataContainer($strTable, $blnNoCache);
	}
}
