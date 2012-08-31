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
class GeneralModelDefault implements InterfaceGeneralModel
{

    /**
     * A list with all Properties.
     *
     * @param array $strPropertyName
     */
    protected $arrProperties = array();
    protected $mixID = null;

    /**
     * A list with all Properties.
     *
     * @param array $strPropertyName
     */
    protected $arrMetaInformation = array();

    /**
     * Copy this model, without the id.
     *
     * @return InterfaceGeneralModel
     */
    public function __clone()
    {
        $this->mixID = null;
    }

    /**
     * Get the id for this modell.
     *
     * @return string The ID for this modell.
     */
    public function getID()
    {
        return $this->mixID;
    }

    /**
     * @see InterfaceGeneralModel::getProperty()
     *
     * @param String $strPropertyName
     * @return null
     */
    public function getProperty($strPropertyName)
    {
        if($strPropertyName == 'id')
        {
            return $this->getID();
        }

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
     * @see InterfaceGeneralModel::getPropertiesAsArray()
     */
    public function getPropertiesAsArray()
    {
        $arrArray       = $this->arrProperties;
        $arrArray["id"] = $this->mixID;

        return $arrArray;
    }

    /**
     * @see InterfaceGeneralModel::getMeta()
	 *
	 * @param string $strMetaName the meta information to retrieve.
	 *
	 * @return mixed|null the set meta information or null if undefined.
     */
    public function getMeta($strMetaName)
    {
        if (key_exists($strMetaName, $this->arrMetaInformation))
        {
            return $this->arrMetaInformation[$strMetaName];
        }
        else
        {
            return null;
        }
    }

    /**
     * Set the id for this modell.
     * This works only once.
     *
     * @param mixed $mixID Could be a integer, string or anything else
     */
    public function setID($mixID)
    {
        if ($this->mixID == null)
        {
            $this->mixID = $mixID;
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
        $this->arrProperties[$strPropertyName] = $varValue;
    }

    /**
     * @see InterfaceGeneralModel::setPropertiesAsArray()
     */
    public function setPropertiesAsArray($arrProperties)
    {
        if (is_array($arrProperties))
        {
            if (array_key_exists("id", $arrProperties))
            {
                unset($arrProperties["id"]);
            }

            $this->arrProperties = $arrProperties;
        }
    }

    /**
     * @see InterfaceGeneralModel::setMeta()
	 *
	 * @param string $strMetaName the meta information name.
	 *
	 * @param mixed $varValue the meta information to store.
	 *
	 * @return void
     */
    public function setMeta($strMetaName, $varValue)
    {
        $this->arrMeta[$strMetaName] = $varValue;
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
