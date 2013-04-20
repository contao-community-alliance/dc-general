<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
	public function get($index);

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
	 * @param InterfaceGeneralModel $model
	 *
	 * @return void
	 */
	public function insert($index, InterfaceGeneralModel $model);

	/**
	 * Remove the given index or model from the collection and renew the index.
	 * ATTENTION: Don't use key to unset in foreach because of the new index.
	 * 
	 * @param mixed $mixedValue
	 */
	public function remove($mixedValue);

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
	public function sort($callback);

}