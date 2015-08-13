<?php
/**
 * PHP version 5
 *
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

/**
 * Handy helper class to generate and manipulate filter arrays.
 *
 * This class is intended to be only used as base class of other filter builders.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
abstract class BaseFilterBuilder
{

    /**
     * The filter builder holding the scope.
     *
     * @var FilterBuilder
     */
    protected $builder;

    /**
     * The current parenting Builder.
     *
     * @var FilterBuilderWithChildren
     */
    protected $parent;

    /**
     * Get the filter builder.
     *
     * @return FilterBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Set the filter builder.
     *
     * @param FilterBuilder $builder The filter builder.
     *
     * @return BaseFilterBuilder
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Set the parent and return self.
     *
     * @param FilterBuilderWithChildren $parent The new parent.
     *
     * @return FilterBuilderWithChildren
     */
    public function setParent(FilterBuilderWithChildren $parent)
    {
        if ($this->parent && $this->parent !== $parent) {
            $this->parent->remove($this);
        }

        $this->parent = $parent;
        $this->parent->add($this);

        return $this;
    }

    /**
     * Retrieve the parent.
     *
     * @return FilterBuilderWithChildren
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Serialize the filter into an array.
     *
     * @return array
     */
    abstract public function get();

    /**
     * Get all the filter array for all filters from the current filter builder.
     *
     * @return array
     */
    public function getAllAsArray()
    {
        return $this->builder->getAllAsArray();
    }

    /**
     * Ensure this filter builder is encapsulated within an AND filter builder.
     *
     * @return AndFilterBuilder
     */
    protected function ensureAndEncapsulation()
    {
        $parent = $this->getParent();

        if ($this instanceof AndFilterBuilder) {
            return $this;
        }

        if ($parent instanceof AndFilterBuilder && !($this instanceof FilterBuilderWithChildren)) {
            return $parent;
        }

        if ($this instanceof FilterBuilderWithChildren) {
            /** @var FilterBuilderWithChildren $this */
            $and = new AndFilterBuilder();
            $this->add($and);

            return $and;
        }

        $and    = new AndFilterBuilder();
        $parent = $this->getParent();
        $parent->add($and);
        $and->add($this);

        return $and;
    }

    /**
     * Ensure this filter builder is encapsulated within an AND filter builder.
     *
     * @return OrFilterBuilder
     */
    protected function ensureOrEncapsulation()
    {
        $parent = $this->getParent();

        if ($this instanceof OrFilterBuilder) {
            return $this;
        }

        if ($parent instanceof OrFilterBuilder && !($this instanceof FilterBuilderWithChildren)) {
            return $parent;
        }

        if ($this instanceof FilterBuilderWithChildren) {
            /** @var FilterBuilderWithChildren $this */
            $orFilter = new OrFilterBuilder();
            $this->add($orFilter);

            return $orFilter;
        }

        $orFilter = new OrFilterBuilder();
        $parent   = $this->getParent();
        $parent->add($orFilter);

        $orFilter->add($this);

        return $orFilter;
    }

    /**
     * Encapsulate the given filter with AND and return it.
     *
     * @param BaseFilterBuilder $filter The filter to encapsulate.
     *
     * @return BaseFilterBuilder
     */
    protected function andEncapsulate($filter)
    {
        $this->ensureAndEncapsulation()->add($filter);

        return $filter;
    }

    /**
     * Encapsulate the given filter with AND and return it.
     *
     * @param BaseFilterBuilder $filter The filter to encapsulate.
     *
     * @return BaseFilterBuilder
     */
    protected function orEncapsulate($filter)
    {
        $this->ensureOrEncapsulation()->add($filter);

        return $filter;
    }

    /**
     * Move one level up in the filter hierarchy.
     *
     * @return FilterBuilderWithChildren
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up()
    {
        return $this->getParent();
    }

    /**
     * Ensure that the given property also equals the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyEqualsFilterBuilder The newly created filter.
     */
    public function andPropertyEquals($property, $value)
    {
        return $this->andEncapsulate(new PropertyEqualsFilterBuilder($property, $value));
    }

    /**
     * Ensure that the given property also equals the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyEqualsFilterBuilder The newly created filter.
     */
    public function orPropertyEquals($property, $value)
    {
        return $this->orEncapsulate(new PropertyEqualsFilterBuilder($property, $value));
    }

    /**
     * Ensure that the given property also equals the given remote property.
     *
     * @param string $property       The name of the property.
     *
     * @param string $remoteProperty The name of the remote property.
     *
     * @param bool   $remoteIsValue  True if the passed remote value is a value, false if it is a property name.
     *
     * @return PropertyEqualsFilterBuilder The newly created filter.
     */
    public function andRemotePropertyEquals($property, $remoteProperty, $remoteIsValue = false)
    {
        $this->getBuilder()->checkNotRoot();

        return $this->andEncapsulate(
            new PropertyEqualsFilterBuilder($property, $remoteProperty, true, !$remoteIsValue)
        );
    }

    /**
     * Ensure that the given property also is greater than the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyGreaterThanFilterBuilder The newly created filter.
     */
    public function andPropertyGreaterThan($property, $value)
    {
        return $this->andEncapsulate(new PropertyGreaterThanFilterBuilder($property, $value));
    }

    /**
     * Ensure that the given property also is greater than the given remote property.
     *
     * @param string $property       The name of the property.
     *
     * @param string $remoteProperty The name of the remote property.
     *
     * @param bool   $remoteIsValue  True if the passed remote value is a value, false if it is a property name.
     *
     * @return PropertyGreaterThanFilterBuilder The newly created filter.
     */
    public function andRemotePropertyGreaterThan($property, $remoteProperty, $remoteIsValue = false)
    {
        $this->getBuilder()->checkNotRoot();

        return $this->andEncapsulate(
            new PropertyGreaterThanFilterBuilder($property, $remoteProperty, true, !$remoteIsValue)
        );
    }

    /**
     * Ensure that the given property also is less than the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyLessThanFilterBuilder The newly created filter.
     */
    public function andPropertyLessThan($property, $value)
    {
        return $this->andEncapsulate(new PropertyLessThanFilterBuilder($property, $value));
    }

    /**
     * Ensure that the given property also is less than the given remote property.
     *
     * @param string $property       The name of the property.
     *
     * @param string $remoteProperty The name of the remote property.
     *
     * @param bool   $remoteIsValue  True if the passed remote value is a value, false if it is a property name.
     *
     * @return PropertyLessThanFilterBuilder The newly created filter.
     */
    public function andRemotePropertyLessThan($property, $remoteProperty, $remoteIsValue = false)
    {
        $this->getBuilder()->checkNotRoot();

        return $this->andEncapsulate(
            new PropertyLessThanFilterBuilder($property, $remoteProperty, true, !$remoteIsValue)
        );
    }

    /**
     * Ensure that the given property also is less than the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyValueInFilterBuilder The newly created filter.
     */
    public function andPropertyValueIn($property, $value)
    {
        return $this->andEncapsulate(new PropertyValueInFilterBuilder($property, $value));
    }

    /**
     * Ensure that the given property also is less than the given value.
     *
     * @param string $property The property name.
     *
     * @param mixed  $value    The property value.
     *
     * @return PropertyValueInFilterBuilder The newly created filter.
     */
    public function andPropertyValueLike($property, $value)
    {
        return $this->andEncapsulate(new PropertyValueLikeFilterBuilder($property, $value));
    }
}
