<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DefaultCollection.
 *
 * This is the default implementation of a model collection in DcGeneral.
 * Internally it simply holds an array.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 */
class DefaultCollection implements CollectionInterface
{
    /**
     * The list of contained models.
     *
     * @var ModelInterface[]
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
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->length();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->arrCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->arrCollection[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->arrCollection[$offset]);
    }

    /**
     * Get the model at a specific index.
     *
     * @param int $intIndex The index of the model to retrieve.
     *
     * @return ModelInterface
     */
    public function get($intIndex)
    {
        if (array_key_exists($intIndex, $this->arrCollection)) {
            return $this->arrCollection[$intIndex];
        }

        return null;
    }

    /**
     * Append a model to the end of this collection.
     *
     * @param ModelInterface $objModel The model to append to the collection.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When no model has been passed.
     */
    public function push(ModelInterface $objModel)
    {
        if (!$objModel) {
            throw new DcGeneralRuntimeException('push() - no model passed', 1);
        }

        $this->arrCollection[] = $objModel;
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
        if (count($this->arrCollection) != 0) {
            return array_pop($this->arrCollection);
        }

        return null;
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
        if ($objModel->hasProperties()) {
            array_unshift($this->arrCollection, $objModel);
        }
    }

    /**
     * Remove the model from the beginning of the collection and return it.
     *
     * If the collection is empty, null will be returned.
     *
     * @return ModelInterface
     */
    public function shift()
    {
        if (count($this->arrCollection) != 0) {
            return array_shift($this->arrCollection);
        }

        return null;
    }

    /**
     * Insert a record at the specific position.
     *
     * Move all records at position >= $index one index up.
     * If $index is out of bounds, just add at the end (does not fill with empty records!).
     *
     * @param int            $intIndex The index where the model shall be placed.
     * @param ModelInterface $objModel The model to insert.
     *
     * @return void
     */
    public function insert($intIndex, ModelInterface $objModel)
    {
        if ($objModel->hasProperties()) {
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
        if (is_object($mixedValue)) {
            foreach ($this->arrCollection as $intIndex => $objModel) {
                if ($mixedValue === $objModel) {
                    unset($this->arrCollection[$intIndex]);
                }
            }
        } else {
            unset($this->arrCollection[$mixedValue]);
        }

        $this->arrCollection = array_values($this->arrCollection);
    }

    /**
     * Retrieve the ids of the models.
     *
     * @return array
     */
    public function getModelIds()
    {
        $ids = array();

        foreach ($this as $model) {
            /** @var ModelInterface $model */
            $ids[] = $model->getId();
        }

        return $ids;
    }

    /**
     * Remove the model with the given id from the collection.
     *
     * @param mixed $modelId The id of the model to remove.
     *
     * @return void
     */
    public function removeById($modelId)
    {
        foreach ($this->arrCollection as $index => $model) {
            if ($modelId === $model->getId()) {
                unset($this->arrCollection[$index]);
            }
        }
    }

    /**
     * Check whether the given model is contained in the collection.
     *
     * @param ModelInterface $model The model to search.
     *
     * @return bool
     */
    public function contains($model)
    {
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            if ($model === $localModel) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the given model is contained in the collection.
     *
     * @param mixed $modelId The model id to search.
     *
     * @return bool
     */
    public function containsById($modelId)
    {
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            if ($modelId === $localModel->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Intersect the given collection with this collection and return the result.
     *
     * @param CollectionInterface $collection The collection to intersect.
     *
     * @return CollectionInterface
     */
    public function intersect($collection)
    {
        $intersection = new DefaultCollection();
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (($localModel->getProviderName() == $otherModel->getProviderName())
                    && ($localModel->getId() == $otherModel->getId())
                ) {
                    $intersection->push($localModel);
                }
            }
        }

        return $intersection;
    }

    /**
     * Compute the union of this collection and the given collection.
     *
     * @param CollectionInterface $collection The collection to intersect.
     *
     * @return CollectionInterface
     */
    public function union($collection)
    {
        $union = clone $this;

        /** @var ModelInterface $otherModel */
        foreach ($collection->diff($this) as $otherModel) {
            $union->push($otherModel);
        }

        return $union;
    }

    /**
     * Computes the difference of the collection.
     *
     * @param CollectionInterface $collection The collection to compute the difference for.
     *
     * @return CollectionInterface The collection containing all the entries from this collection that are not present
     *                             in the given collection.
     */
    public function diff($collection)
    {
        $diff = new DefaultCollection();
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (($localModel->getProviderName() == $otherModel->getProviderName())
                    && ($localModel->getId() == $otherModel->getId())
                ) {
                    continue;
                }
                $diff->push($localModel);
            }
        }

        return $diff;
    }

    /**
     * Check if the given collection is an subset of the given collection.
     *
     * @param CollectionInterface $collection The collection to check.
     *
     * @return boolean
     */
    public function isSubsetOf($collection)
    {
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (($localModel->getProviderName() == $otherModel->getProviderName())
                    && ($localModel->getId() == $otherModel->getId())
                ) {
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Make a reverse sorted collection of this collection.
     *
     * @return CollectionInterface
     */
    public function reverse()
    {
        $newCollection = clone $this;

        $newCollection->arrCollection = array_reverse($this->arrCollection);

        return $newCollection;
    }

    /**
     * Sort the records with the given callback and return the new sorted collection.
     *
     * @param callback $callback The callback function to use.
     *
     * @return CollectionInterface
     */
    public function sort($callback)
    {
        $newCollection = clone $this;
        uasort($newCollection->arrCollection, $callback);

        $newCollection->arrCollection = array_values($newCollection->arrCollection);

        return $newCollection;
    }

    /**
     * Get a iterator for this collection.
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arrCollection);
    }
}
