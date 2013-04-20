<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
	 * @return InterfaceGeneralModel
	 */
	public function get($intIndex)
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
	public function insert($intIndex, InterfaceGeneralModel $objModel)
	{
		if ($objModel->hasProperties())
		{
			array_insert($this->arrCollection, $intIndex, array($objModel));
		}
	}

	/**
	 * Remove the given index or model from the collection and renew the index.
	 * ATTENTION: Don't use key to unset in foreach because of the new index.
	 *
	 * @param mixed $mixedValue
	 */
	public function remove($mixedValue)
	{
		if (is_object($mixedValue))
		{
			foreach ($this->arrCollection as $intIndex => $objModel)
			{
				if ($mixedValue === $objModel)
				{
					unset($this->arrCollection[$intIndex]);
				}
			}
		}
		else
		{
			unset($this->arrCollection[$mixedValue]);
		}

		$this->arrCollection = array_values($this->arrCollection);
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
		if (!$objModel)
		{
			throw new Exception("push() - no model passed", 1);
		}

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

		$this->arrCollection = array_values($this->arrCollection);
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