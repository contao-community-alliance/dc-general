<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @see InterfaceGeneralData
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralDataMultiLanguage extends GeneralDataDefault implements InterfaceGeneralDataML
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
        if($this->strCurrentLanguage == "de") $objModel->setProperty("active", true);

        $objCollection->add($objModel);

        $objModel = $this->getEmptyModel();
        $objModel->setID("en");
        $objModel->setProperty("name", "English");
        if($this->strCurrentLanguage == "en") $objModel->setProperty("active", true);        
        
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

?>
