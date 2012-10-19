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
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
interface InterfaceGeneralDataMultiLanguage extends InterfaceGeneralData
{

    /**
     * Get all avaidable languages for a special record. 
     * 
     * @param mixed $mixID The ID of record
     * @return InterfaceGeneralCollection 
     */
    public function getLanguages($mixID);
    
    /**
     * Get the fallback language
     * 
     * @param mixed $mixID The ID of record
     * @return InterfaceGeneralModel
     */
    public function getFallbackLanguage($mixID);

    /**
     * Set the working language for the whole dataprovider.
     *  
     * @param $strLanguage The new language, use hort tag "2 chars like de, fr etc." 
     * @return void
     */
    public function setCurrentLanguage($strLanguage);

    /**
     * Get the working language
     * 
     * return String Short tag for the current working language like de or fr etc. 
     */
    public function getCurrentLanguage();
}

?>
