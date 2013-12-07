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

namespace DcGeneral\Data;

/**
 * Class MultiLanguageDriver.
 * Implementation of a multi language Contao database driver.
 *
 * The default language will be initialized to "en".
 *
 * @package DcGeneral\Data
 */
class MultiLanguageDriver extends DefaultDriver implements MultiLanguageDriverInterface
{
	/**
	 * Buffer to keep the current active working language.
	 *
	 * @var string
	 */
	protected $strCurrentLanguage;

	/**
	 * Constructor - initializes the object with English as working language.
	 */
	public function __construct()
	{
		$this->setCurrentLanguage('en');

		parent::__construct();
	}

	/**
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @todo: using Collection here is perversion. We need to change this!
	 * @return CollectionInterface
	 */
	public function getLanguages($mixID)
	{
		$objCollection = $this->getEmptyCollection();

		$objModel = $this->getEmptyModel();
		$objModel->setID("de");
		$objModel->setProperty("name", "Deutsch");
		if ($this->strCurrentLanguage == "de")
			$objModel->setProperty("active", true);

		$objCollection->add($objModel);

		$objModel = $this->getEmptyModel();
		$objModel->setId("en");
		$objModel->setProperty("name", "English");
		if ($this->strCurrentLanguage == "en")
			$objModel->setProperty("active", true);

		$objCollection->add($objModel);

		return $objCollection;
	}

	/**
	 * Get the fallback language of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @todo: using Model here is perversion. We need to change this!
	 * @return ModelInterface
	 */
	public function getFallbackLanguage($mixID)
	{
		$objModel = $this->getEmptyModel();
		$objModel->setId("en");
		$objModel->setProperty("name", "English");

		return $objModel;
	}

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc.".
	 *
	 * @return DriverInterface
	 */
	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;

		return $this;
	}

	/**
	 * Get the current working language.
	 *
	 * @return string Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage()
	{
		return $this->strCurrentLanguage;
	}
}
