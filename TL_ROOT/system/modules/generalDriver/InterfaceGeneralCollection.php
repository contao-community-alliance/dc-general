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
interface InterfaceGeneralCollection extends IteratorAggregate
{

    /**
     * Get length of this collection.
     *
     * @return int
     */
    public function length();

    /**
     * Get item at a specific index.
     *
     * @param int $index
     *
     * @return Model
     */
    public function get(int $index);

    /**
     * Alias for push.
     *
     * @see push
     */
    public function add(InterfaceGeneralModel $model);

    /**
     * Add a record to the end of this collection.
     *
     * @param Model $model
     *
     * @return void
     */
    public function push(InterfaceGeneralModel $model);

    /**
     * Remove a record from the end of this collection and return it.
     *
     * @return Model|null
     */
    public function pop();

    /**
     * Add a record at the beginning of this collection.
     *
     * @param Model $Model
     *
     * @return void
     */
    public function unshift(InterfaceGeneralModel $model);

    /**
     * Remove a record from the beginning of this collection and return it.
     *
     * @return Model|null
     */
    public function shift();

    /**
     * Insert a record at the specific position.
     * Move all records at position >= $index one index up.
     * If $index is out of bounds, just add at the end
     * (do not fill with empty records!).
     *
     * @param int $index
     * @param Model $model
     *
     * @return void
     */
    public function insert(int $index, InterfaceGeneralModel $model);

    /**
     * Make a reverse sorted collection.
     *
     * @return Collection
     */
    public function reverse();

    /**
     * Sort the records and return the new sorted collection.
     *
     * @param callback $callback
     *
     * @return Collection
     */
    public function sort(callback $callback);
}


?>
