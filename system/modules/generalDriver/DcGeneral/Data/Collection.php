<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Data;

use DcGeneral\Data\Interfaces\Model as ModelInterface;

class Collection implements Interfaces\Collection
{
	/**
	 * The list of contained models.
	 *
	 * @var Model[]
	 */
	protected $arrCollection = array();

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
	 * Get the model at a specific index.
	 *
	 * @param integer $intIndex The index of the model to retrieve.
	 *
	 * @return ModelInterface
	 */
	public function get($intIndex)
	{
		if (array_key_exists($intIndex, $this->arrCollection))
		{
			return $this->arrCollection[$intIndex];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Alias for push - Append a model to the end of this collection.
	 *
	 * @param ModelInterface $objModel The model to append to the collection.
	 *
	 * @return void
	 *
	 * @throws \Exception when no model has been passed.
	 *
	 * @see push
	 */
	public function add(ModelInterface $objModel)
	{
		$this->push($objModel);
	}

	/**
	 * Append a model to the end of this collection.
	 *
	 * @param ModelInterface $objModel The model to append to the collection.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when no model has been passed.
	 */
	public function push(ModelInterface $objModel)
	{
		if (!$objModel)
		{
			throw new \RuntimeException("push() - no model passed", 1);
		}

		if ($objModel->hasProperties())
		{
			array_push($this->arrCollection, $objModel);
		}
	}

	/**
	 * Remove the model at the end of the collection and return it.
	 *
	 * If the collection is empty, null will be returned.
	 *
	 * @return ModelInterface
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
	 * Insert a model at the beginning of the collection.
	 *
	 * @param ModelInterface $objModel The model to insert into the collection.
	 *
	 * @return void
	 */
	public function unshift(ModelInterface $objModel)
	{
		if ($objModel->hasProperties())
		{
			array_unshift($this->arrCollection, $objModel);
		}
	}

	/**
	 * Remove the model from the beginning of the collection and return it.
	 *
	 * If the collection is empty, null will be returned.
	 *
	 * @return Model
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
	 * Insert a record at the specific position.
	 *
	 * Move all records at position >= $index one index up.
	 * If $index is out of bounds, just add at the end (does not fill with empty records!).
	 *
	 * @param integer               $intIndex  The index where the model shall be placed.
	 *
	 * @param ModelInterface $objModel The model to insert.
	 *
	 * @return void
	 */
	public function insert($intIndex, ModelInterface $objModel)
	{
		if ($objModel->hasProperties())
		{
			array_insert($this->arrCollection, $intIndex, array($objModel));
		}
	}

	/**
	 * Remove the given index or model from the collection and renew the index.
	 *
	 * ATTENTION: Don't use key to unset in foreach because of the new index.
	 *
	 * @param mixed $mixedValue The index (integer) or InterfaceGeneralModel instance to remove.
	 *
	 * @return void
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
	 * Make a reverse sorted collection of this collection.
	 *
	 * @return Collection
	 */
	public function reverse()
	{
		$this->arrCollection = array_reverse($this->arrCollection);
	}

	/**
	 * Sort the records with the given callback and return the new sorted collection.
	 *
	 * @param callback $callback
	 *
	 * @return Collection
	 */
	public function sort($callback)
	{
		uasort($this->arrCollection, $callback);

		$this->arrCollection = array_values($this->arrCollection);
	}

	/**
	 * Get a iterator for this collection
	 *
	 * @return \IteratorAggregate
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->arrCollection);
	}
}
