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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\AndFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\BaseFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\OrFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\PropertyEqualsFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\PropertyGreaterThanFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\PropertyLessThanFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\PropertyValueInFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder\PropertyValueLikeFilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate filter arrays.
 */
class FilterBuilder
{
    /**
     * The current filter root (always an AND builder).
     *
     * @var AndFilterBuilder
     */
    protected $filters;

    /**
     * Flag determining if the current filter is a root filter or parent child filter.
     *
     * @var bool
     */
    protected $isRootFilter;

    /**
     * Create a new instance.
     *
     * @param array $filter Optional base filter array.
     *
     * @param bool  $isRoot Flag determining if the current filter is a root filter.
     *
     * @throws DcGeneralInvalidArgumentException When an invalid filter array has been passed.
     */
    public function __construct($filter = array(), $isRoot = false)
    {
        if (!is_array($filter)) {
            throw new DcGeneralInvalidArgumentException(
                'FilterBuilder needs a valid filter array ' . gettype($filter) . 'given'
            );
        }

        $this->filters      = static::getBuilderFromArray(array('operation' => 'AND', 'children' => $filter), $this);
        $this->isRootFilter = $isRoot;
    }

    /**
     * Instantiate the correct builder class from a given filter array.
     *
     * @param array         $filter  The filter.
     *
     * @param FilterBuilder $builder The builder instance.
     *
     * @return BaseFilterBuilder
     *
     * @throws DcGeneralInvalidArgumentException When an invalid operation is encountered.
     */
    public static function getBuilderFromArray($filter, $builder)
    {
        switch ($filter['operation']) {
            case 'AND':
                return AndFilterBuilder::fromArray($filter, $builder);
            case 'OR':
                return OrFilterBuilder::fromArray($filter, $builder);
            case '=':
                return PropertyEqualsFilterBuilder::fromArray($filter);
            case '>':
                return PropertyGreaterThanFilterBuilder::fromArray($filter);
            case '<':
                return PropertyLessThanFilterBuilder::fromArray($filter);
            case 'IN':
                return PropertyValueInFilterBuilder::fromArray($filter);
            case 'LIKE':
                return PropertyValueLikeFilterBuilder::fromArray($filter);
            default:
        }

        throw new DcGeneralInvalidArgumentException(
            'Invalid operation ' . $filter['operation'] . ' it must be one of: AND, OR, =, >, <, IN, LIKE'
        );
    }

    /**
     * Create a new instance from an array.
     *
     * @param array $filter The initial filter array (optional).
     *
     * @return FilterBuilder
     */
    public static function fromArray($filter = array())
    {
        return new static($filter, false);
    }

    /**
     * Create a new instance from an array for a root filter.
     *
     * @param array $filter The initial filter array (optional).
     *
     * @return FilterBuilder
     */
    public static function fromArrayForRoot($filter = array())
    {
        return new static($filter, true);
    }

    /**
     * Return the root AND condition.
     *
     * @return AndFilterBuilder
     */
    public function getFilter()
    {
        return $this->filters;
    }

    /**
     * Encapsulate the root with an Or condition and return the OR condition.
     *
     * @return OrFilterBuilder
     */
    public function encapsulateOr()
    {
        $root = $this->filters;

        $this->filters = new AndFilterBuilder();
        $this->filters->setBuilder($this);

        $orFilter = new OrFilterBuilder(array($root));
        $this->filters->add($orFilter);

        return $orFilter;
    }

    /**
     * Determine if this builder is for a root filter or not.
     *
     * @return bool
     */
    public function isRootFilter()
    {
        return $this->isRootFilter;
    }

    /**
     * Check that the builder is not for a root filter.
     *
     * @return FilterBuilder
     *
     * @throws DcGeneralInvalidArgumentException When the builder is for an root filter.
     */
    public function checkNotRoot()
    {
        if ($this->isRootFilter) {
            throw new DcGeneralInvalidArgumentException(
                'ERROR: Filter builder is for an root filter.'
            );
        }

        return $this;
    }

    /**
     * Return the current filters.
     *
     * @return array
     */
    public function getAllAsArray()
    {
        $array = $this->filters->get();

        return $array['children'];
    }

    /**
     * Check if an given argument is a valid operation.
     *
     * @param string $operation The operation to check.
     *
     * @return bool
     */
    public static function isValidOperation($operation)
    {
        return in_array($operation, array('AND', 'OR', '=', '>', '<', 'IN', 'LIKE'));
    }

    /**
     * Check that an given argument is a valid operation.
     *
     * @param string $operation The operation to check.
     *
     * @return FilterBuilder
     *
     * @throws DcGeneralInvalidArgumentException When an invalid operation name has been passed.
     */
    public function checkValidOperation($operation)
    {
        if (!static::isValidOperation($operation)) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid operation ' . $operation . ' it must be one of: AND, OR, =, >, <, IN, LIKE'
            );
        }

        return $this;
    }
}
