<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ArrayIterator;
use Contao\ArrayUtil;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Traversable;

use function array_key_exists;
use function array_pop;
use function array_reverse;
use function array_shift;
use function array_unshift;
use function array_values;
use function count;
use function is_object;
use function uasort;

/**
 * Class DefaultCollection.
 *
 * This is the default implementation of a model collection in DcGeneral.
 * Internally it simply holds an array.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) - There is no elegant way to reduce this class more without
 *                                                     reducing the interface.
 */
class DefaultCollection implements CollectionInterface
{
    /**
     * The list of contained models.
     *
     * @var array<int, ModelInterface>
     */
    protected array $arrCollection = [];

    /**
     * {@inheritDoc}
     */
    public function length(): int
    {
        return count($this->arrCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->arrCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?ModelInterface
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->arrCollection[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->arrCollection[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($index): ?ModelInterface
    {
        if (array_key_exists($index, $this->arrCollection)) {
            return $this->arrCollection[$index];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function push(ModelInterface $model): void
    {
        $this->arrCollection[] = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function pop(): ?ModelInterface
    {
        if (count($this->arrCollection)) {
            return array_pop($this->arrCollection);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function unshift(ModelInterface $model): void
    {
        if ($model->hasProperties()) {
            array_unshift($this->arrCollection, $model);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function shift(): ?ModelInterface
    {
        if (count($this->arrCollection)) {
            return array_shift($this->arrCollection);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function insert($index, ModelInterface $model): void
    {
        if ($model->hasProperties()) {
            ArrayUtil::arrayInsert($this->arrCollection, $index, [$model]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function remove($mixedValue): void
    {
        if (is_object($mixedValue)) {
            foreach ($this->arrCollection as $collectionIndex => $model) {
                if ($mixedValue === $model) {
                    unset($this->arrCollection[$collectionIndex]);
                }
            }
        } else {
            unset($this->arrCollection[$mixedValue]);
        }

        $this->arrCollection = array_values($this->arrCollection);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelIds(): array
    {
        $ids = [];
        foreach ($this as $model) {
            /** @var ModelInterface $model */
            $ids[] = $model->getId();
        }

        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    public function removeById($modelId): void
    {
        foreach ($this->arrCollection as $index => $model) {
            if ($modelId === $model->getId()) {
                unset($this->arrCollection[$index]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function contains($model): bool
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
     * {@inheritDoc}
     */
    public function containsById($modelId): bool
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
     * {@inheritDoc}
     */
    public function intersect($collection): CollectionInterface
    {
        $intersection = new DefaultCollection();
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (
                    ($localModel->getProviderName() === $otherModel->getProviderName())
                    && ($localModel->getId() === $otherModel->getId())
                ) {
                    $intersection->push($localModel);
                }
            }
        }

        return $intersection;
    }

    /**
     * {@inheritDoc}
     */
    public function union($collection): CollectionInterface
    {
        $union = clone $this;

        /** @var ModelInterface $otherModel */
        foreach ($collection->diff($this) as $otherModel) {
            $union->push($otherModel);
        }

        return $union;
    }

    /**
     * {@inheritDoc}
     */
    public function diff($collection): CollectionInterface
    {
        $diff = new DefaultCollection();
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (
                    ($localModel->getProviderName() === $otherModel->getProviderName())
                    && ($localModel->getId() === $otherModel->getId())
                ) {
                    continue;
                }
                $diff->push($localModel);
            }
        }

        return $diff;
    }

    /**
     * {@inheritDoc}
     */
    public function isSubsetOf($collection): bool
    {
        /** @var ModelInterface $localModel */
        foreach ($this as $localModel) {
            /** @var ModelInterface $otherModel */
            foreach ($collection as $otherModel) {
                if (
                    ($localModel->getProviderName() === $otherModel->getProviderName())
                    && ($localModel->getId() === $otherModel->getId())
                ) {
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function reverse(): CollectionInterface
    {
        $newCollection = clone $this;

        $newCollection->arrCollection = array_reverse($this->arrCollection);

        return $newCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function sort($callback): CollectionInterface
    {
        $newCollection = clone $this;
        uasort($newCollection->arrCollection, $callback);

        $newCollection->arrCollection = array_values($newCollection->arrCollection);

        return $newCollection;
    }

    /**
     * {@inheritDoc}
     *
     * @return Traversable<int, ModelInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->arrCollection);
    }
}
