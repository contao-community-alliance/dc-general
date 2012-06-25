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
 * @see InterfaceGeneralCollection
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralCollectionDefault implements InterfaceGeneralCollection
{

    /**
     * A list with a models.
     * 
     * @var array 
     */
    protected $arrCollection = array();

    /**
     * Alias for push.
     *
     * @see push
     */
    public function add(InterfaceGeneralModel $objModel)
    {
        $this->push($objModel);
    }

    /**
     * Get item at a specific index.
     *
     * @param int $intIndex
     *
     * @return InterfaceGeneralModel|null
     */
    public function get(int $intIndex)
    {
        if (key_exists($intIndex, $this->arrCollection))
        {
            return $this->arrCollection[$intIndex];
        }
        else
        {
            return null;
        }
    }

    /**
     * Insert a record at the specific position.
     * Move all records at position >= $intIndex one index up.
     * If $intIndex is out of bounds, just add at the end
     * (do not fill with empty records!).
     *
     * @param int $intIndex
     * @param InterfaceGeneralModel $objModel
     *
     * @return void
     */
    public function insert(int $intIndex, InterfaceGeneralModel $objModel)
    {
        if ($objModel->hasProperties())
        {
            array_insert($this->arrCollection, $intIndex, array($objModel));
        }
    }

    /**
     * Get length of this collection.
     *
     * @return int
     */
    public function length()
    {
        return count($this->arrCollection);
    }

    /**
     * Remove a record from the end of this collection and return it.
     *
     * @return InterfaceGeneralModel|null
     */
    public function pop()
    {
        if (count($this->arrCollection) != 0)
        {
            return array_pop($this->arrCollection);
        }
        else
        {
            return null;
        }
    }

    /**
     * Add a record to the end of this collection.
     * (do not fill with empty records!)
     * 
     * @param InterfaceGeneralModel $objModel
     *
     * @return void
     */
    public function push(InterfaceGeneralModel $objModel)
    {
        if ($objModel->hasProperties())
        {
            array_push($this->arrCollection, $objModel);
        }
    }

    /**
     * Make a reverse sorted collection.
     *
     * @return Collection
     */
    public function reverse()
    {
        $this->arrCollection = array_reverse($this->arrCollection);
    }

    /**
     * Remove a record from the beginning of this collection and return it.
     *
     * @return InterfaceGeneralModel|null
     */
    public function shift()
    {
        if (count($this->arrCollection) != 0)
        {
            return array_shift($this->arrCollection);
        }
        else
        {
            return null;
        }
    }

    /**
     * Sort the records and return the new sorted collection.
     *
     * @param mixed $callback
     *
     * @return Collection
     */
    public function sort($callback)
    {
        uasort($this->arrCollection, $callback);
    }

    /**
     * Add a record at the beginning of this collection.
     * (do not fill with empty records!)
     * 
     * @param InterfaceGeneralModel $objModel
     *
     * @return void
     */
    public function unshift(InterfaceGeneralModel $objModel)
    {
        if ($objModel->hasProperties())
        {
            array_unshift($this->arrCollection, $objModel);
        }
    } 
    
    /**
     * Get a iterator for this collection
     * 
     * @return ArrayIterator 
     */
    public function getIterator()
    {
        return new ArrayIterator($this->arrCollection);
    }
}

?>
