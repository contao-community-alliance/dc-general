<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2034 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2034 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Handy helper class to generate and manipulate filter arrays containing children.
 *
 * This class is intended to be only used as base class of other filters and via the FilterBuilder main class.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 */
class FilterBuilderWithChildren extends BaseFilterBuilder implements \Iterator, \ArrayAccess
{
    /**
     * The operation string.
     *
     * @var string
     */
    protected $operation;

    /**
     * The children within this filter builder.
     *
     * @var BaseFilterBuilder[]
     */
    protected $children;

    /**
     * The current index.
     *
     * @var int
     */
    protected $index;

    /**
     * Create a new instance.
     *
     * @param array $children The initial children to absorb.
     *
     * @throws DcGeneralInvalidArgumentException When invalid children have been passed.
     */
    public function __construct($children = [])
    {
        if (!\is_array($children)) {
            throw new DcGeneralInvalidArgumentException(
                __CLASS__ . ' needs a valid child filter array ' . \gettype($children) . 'given'
            );
        }

        $this->children = [];

        foreach ($children as $child) {
            $this->add($child);
        }
        $this->index = -1;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $children       = $this->children;
        $this->children = [];
        foreach ($children as $child) {
            $bobaFett = clone $child;
            $bobaFett->setParent($this);
        }
    }

    /**
     * Return the current element.
     *
     * @return BaseFilterBuilder
     *
     * @throws DcGeneralRuntimeException When the current position is invalid.
     */
    public function current(): ?BaseFilterBuilder
    {
        if (-1 === $this->index) {
            return $this->first();
        }

        if (!$this->valid()) {
            throw new DcGeneralRuntimeException('FilterBuilder position is invalid.');
        }

        return $this->children[$this->key()];
    }

    /**
     * Move forward to next element and return it.
     *
     * @return BaseFilterBuilder
     */
    public function next(): ?BaseFilterBuilder
    {
        $this->index++;

        return $this->valid() ? $this->current() : null;
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return ($this->index > -1) && ($this->index < \count($this->children));
    }

    /**
     * Rewind the Iterator to the first element and return it.
     *
     * This is an alias for {@link FilterBuilderWithChildren::first()} only present for implementing Iterator interface.
     *
     * @return BaseFilterBuilder
     */
    public function rewind(): ?BaseFilterBuilder
    {
        return $this->first();
    }

    /**
     * Rewind the Iterator to the first element and return it.
     *
     * @return BaseFilterBuilder
     */
    public function first()
    {
        $this->index = \count($this->children) ? 0 : (-1);

        if ($this->index === -1) {
            return null;
        }

        return $this->current();
    }

    /**
     * Whether a offset exists.
     *
     * @param int $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->children[$offset]);
    }

    /**
     * Retrieve an element by offset.
     *
     * @param int $offset The offset to retrieve.
     *
     * @return BaseFilterBuilder
     */
    public function offsetGet($offset): BaseFilterBuilder
    {
        return $this->children[$offset];
    }

    /**
     * Set the element at a certain offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return FilterBuilderWithChildren The current builder.
     */
    public function offsetSet($offset, $value): FilterBuilderWithChildren
    {
        $this->children[$offset] = $value;

        return $this;
    }

    /**
     * Unset an offset.
     *
     * @param int $offset The offset to unset.
     *
     * @return FilterBuilderWithChildren The current builder.
     */
    public function offsetUnset($offset): FilterBuilderWithChildren
    {
        unset($this->children[$offset]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBuilder($builder)
    {
        parent::setBuilder($builder);

        foreach ($this->children as $child) {
            $child->setBuilder($builder);
        }

        return $this;
    }

    /**
     * Get the position index of a given filter builder in this instance.
     *
     * @param BaseFilterBuilder $filter The filter builder to search.
     *
     * @return int
     */
    public function indexOf($filter)
    {
        foreach ($this->children as $i => $child) {
            if ($child === $filter) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * Add a given filter builder to this instance.
     *
     * @param BaseFilterBuilder $filter The filter builder to add.
     *
     * @return FilterBuilderWithChildren The current builder.
     */
    public function add($filter)
    {
        $index = $this->indexOf($filter);

        if ($index === -1) {
            $this->children[] = $filter;
            $filter->setBuilder($this->builder)->setParent($this);
        }

        return $this;
    }

    /**
     * Remove a given filter builder from this instance.
     *
     * @param BaseFilterBuilder $filter The filter builder to remove.
     *
     * @return FilterBuilderWithChildren The current builder.
     */
    public function remove($filter)
    {
        $index = $this->indexOf($filter);

        if ($index > -1) {
            $this->offsetUnset($index);
        }

        return $this;
    }

    /**
     * Initialize an instance with the values from the given array.
     *
     * @param array         $array   The initialization array.
     * @param FilterBuilder $builder The builder instance.
     *
     * @return BaseFilterBuilder
     */
    public static function fromArray($array, $builder)
    {
        $children = [];
        foreach ($array['children'] as $child) {
            $children[] = FilterBuilder::getBuilderFromArray($child, $builder);
        }

        return (new static($children))->setBuilder($builder);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $children = [];
        foreach ($this->children as $child) {
            /** @var BaseFilterBuilder $child */
            $children[] = $child->get();
        }

        return [
            'operation' => $this->operation,
            'children'  => $children
        ];
    }

    /**
     * Absorb the given filter builder or filter builder collection.
     *
     * @param FilterBuilder|FilterBuilderWithChildren $filters The input.
     *
     * @return FilterBuilderWithChildren
     */
    public function append($filters)
    {
        if ($filters instanceof FilterBuilder) {
            $filters = $filters->getFilter();
        }

        $this->andEncapsulate((clone $filters)->setBuilder($this->getBuilder()));

        return $this;
    }
}
