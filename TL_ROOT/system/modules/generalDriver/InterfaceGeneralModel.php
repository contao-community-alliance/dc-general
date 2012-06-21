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
interface InterfaceGeneralModel extends IteratorAggregate
{

    /**
     * Copy this model, without the id.
     * 
     * @return InterfaceGeneralModel
     */
    public function __clone();
    
    /**
     * Get the id for this modell.
     * 
     * @return string The ID for this modell.
     */
    public function getID();

    /**
     * Fetch property from model. 
     */
    public function getProperty($strPropertyName);

    /**
     * Fetch all properties from model.
     * 
     * return array
     */
    public function getPropertiesAsArray();
    
   /**
    * Set the id for this object  
    * 
    * @param mixed $mixID Could be a integer, string or anything else
    */
    public function setID($mixID);
    
    /**
     * Update property in model.
     */
    public function setProperty($strPropertyName, $varValue);
    
    /**
     * Update all properties in model.
     * 
     * @param array $arrProperties
     */
    public function setPropertiesAsArray($arrProperties);
    
    /**
     * Check if this model have any properties.
     * 
     * @return boolean True|False 
     */
    public function hasProperties();
}

?>
