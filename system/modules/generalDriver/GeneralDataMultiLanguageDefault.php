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

	protected $strCurrentLanguage;

	public function __construct()
	{
		$this->strCurrentLanguage = "en";

		parent::__construct();
	}

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

	public function getFallbackLanguage($mixID)
	{
		$objModel = $this->getEmptyModel();
		$objModel->setID("en");
		$objModel->setProperty("name", "English");

		return $objModel;
	}

	public function getCurrentLanguage()
	{
		return $this->strCurrentLanguage;
	}

	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;
	}

	public function fetch(GeneralDataConfigDefault $objConfig)
	{
		return parent::fetch($objConfig);
	}

	public function fetchAll(GeneralDataConfigDefault $objConfig)
	{
		return parent::fetchAll($objConfig);
	}

	public function fetchEach(GeneralDataConfigDefault $objConfig)
	{
		return parent::fetchEach($objConfig);
	}

	public function save(InterfaceGeneralModel $objItem, $recursive = false)
	{
		return parent::save($objItem, $recursive);
	}

	public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
	{
		parent::saveEach($objItems, $recursive);
	}

}