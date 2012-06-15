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
 * @see InterfaceGeneralModel
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralModel_Default implements InterfaceGeneralModel
{

    /**
     * A list with all Properties.
     * 
     * @param array $strPropertyName 
     */
    protected $arrProperties = array();

    /**
     * @see InterfaceGeneralModel::getProperty()
     * 
     * @param String $strPropertyName
     * @return null 
     */
    public function getProperty($strPropertyName)
    {
        if (key_exists($strPropertyName, $this->arrProperties))
        {
            return $this->arrProperties[$strPropertyName];
        }
        else
        {
            return null;
        }
    }

    /**
     * @see InterfaceGeneralModel::setProperty()
     * 
     * @param String $strPropertyName
     * @param mixed $varValue 
     */
    public function setProperty($strPropertyName, $varValue)
    {
        if ($varValue == null)
        {
            return;
        }

        $this->arrProperties[$strPropertyName] = $varValue;
    }

    /**
     * @see InterfaceGeneralModel::hasProperties()
     * 
     * @return boolean
     */
    public function hasProperties()
    {
        if (count($this->arrProperties) != 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Get a iterator for this collection
     * 
     * @return ArrayIterator 
     */
    public function getIterator()
    {
        return new ArrayIterator($this->arrProperties);
    }

}

?>
