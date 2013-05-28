<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralDataMultiLanguageDefault extends GeneralDataDefault implements InterfaceGeneralDataMultiLanguage
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
	 * @return InterfaceGeneralCollection
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
		$objModel->setID("en");
		$objModel->setProperty("name", "English");
		if ($this->strCurrentLanguage == "en")
			$objModel->setProperty("active", true);

		$objCollection->add($objModel);

		return $objCollection;
	}

	/**
	 * Get the fallback language. In the default implementation, this is hardcoded to English.
	 *
	 * @param mixed $mixID Unused in the base implementation.
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getFallbackLanguage($mixID)
	{
		$objModel = $this->getEmptyModel();
		$objModel->setID("en");
		$objModel->setProperty("name", "English");

		return $objModel;
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

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc."
	 *
	 * @return void
	 */
	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;
	}
}
