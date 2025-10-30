<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Static helper class for DefaultDataProvider.
 *
 * NOTE: Can't define filters recursively. See: https://github.com/vimeo/psalm/issues/2777
 * @psalm-type TSQLFilterAND=array{operation: 'AND', children: list<array{}>}
 * @psalm-type TSQLFilterOR=array{operation: 'OR', children: list<array{}>}
 * @psalm-type TSQLFilterCMP=array{operation: "="|">"|">="|"<"|"<="|"<>", property: string, value: string|int}
 * @psalm-type TSQLFilterIN=array{operation: 'IN'|'NOT IN', property: string, values: list<string|int>}
 * @psalm-type TSQLFilterLIKE=array{operation: 'LIKE'|'NOT LIKE', property: string, value: string}
 * @psalm-type TSQLFilterNULL=array{operation: 'IS NULL'|'IS NOT NULL', property: string, value: string}
 * @psalm-type TSQLFilter=TSQLFilterAND|TSQLFilterOR|TSQLFilterCMP|TSQLFilterIN|TSQLFilterLIKE|TSQLFilterNULL
 */
class DefaultDataProviderDBalUtils
{
    /**
     * Add the field list.
     *
     * Returns all values from $objConfig->getFields() as comma separated list.
     *
     * @param ConfigInterface $config       The configuration to use.
     * @param string          $idProperty   The name of the id property.
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return void
     */
    public static function addField(ConfigInterface $config, $idProperty, QueryBuilder $queryBuilder)
    {
        if ($config->getIdOnly()) {
            $queryBuilder->select($idProperty);
            return;
        }

        if (null !== $fieldList = $config->getFields()) {
            /** @psalm-suppress DeprecatedMethod - bc layer */
            $connection = $queryBuilder->getConnection();
            $fields     = \implode(
                ', ',
                \array_map(
                    function ($field) use ($connection) {
                        return $connection->quoteIdentifier($field);
                    },
                    $fieldList
                )
            );

            if (false !== \stripos($fields, 'DISTINCT') || \in_array($idProperty, $fieldList, true)) {
                $queryBuilder->select($fields);
                return;
            }

            $queryBuilder->select($idProperty . ', ' . $fields);
            return;
        }

        $queryBuilder->select('*');
    }

    /**
     * Add the WHERE clause for a configuration.
     *
     * @param ConfigInterface $config       The configuration to use.
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return void
     */
    public static function addWhere($config, QueryBuilder $queryBuilder)
    {
        self::addFilter($config, $queryBuilder);
    }

    /**
     * Add the order by part of a query.
     *
     * @param ConfigInterface $config       The configuration to use.
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return void
     */
    public static function addSorting(ConfigInterface $config, QueryBuilder $queryBuilder)
    {
        $sorting = $config->getSorting();
        if (empty($sorting)) {
            return;
        }

        foreach ($sorting as $sort => $order) {
            if (empty($sort)) {
                continue;
            }

            // array could be a simple field list or list of field => direction combinations.
            if (!empty($order)) {
                $order = \strtoupper($order);
                if (!\in_array($order, [DCGE::MODEL_SORTING_ASC, DCGE::MODEL_SORTING_DESC])) {
                    $sort  = $order;
                    $order = DCGE::MODEL_SORTING_ASC;
                }
            } else {
                $order = DCGE::MODEL_SORTING_ASC;
            }

            $queryBuilder->addOrderBy($sort, $order);
        }
    }

    /**
     * Build the WHERE conditions via calculateSubfilter().
     *
     * @param ConfigInterface $config       The configuration to use.
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return string The combined conditions.
     */
    private static function addFilter(ConfigInterface $config, QueryBuilder $queryBuilder): string
    {
        /** @var list<TSQLFilter> $filter */
        $filter = $config->getFilter() ?? [];

        /** @var TSQLFilterAND $andFilter */
        $andFilter = ['operation' => 'AND', 'children' => $filter];

        // Combine filter syntax.
        return static::calculateSubFilter($andFilter, $queryBuilder);
    }

    /**
     * Combine a filter in standard filter array notation.
     *
     * Supported operations are:
     * operation      needed arguments     argument type.
     * AND
     *                'children'           array
     * OR
     *                'children'           array
     * = | <>
     *                'property'           string (the name of a property)
     *                'value'              literal
     * > | >=
     *                'property'           string (the name of a property)
     *                'value'              literal
     * < | <=
     *                'property'           string (the name of a property)
     *                'value'              literal
     * IN | NOT IN
     *                'property'           string (the name of a property)
     *                'values'             array of literal
     *
     * LIKE | NOT LIKE
     *                'property'           string (the name of a property)
     *                'value'              literal - Wildcards * (Many) ? (One)
     *
     * @param TSQLFilter   $filter       The filter to be combined to a valid SQL filter query.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string The combined WHERE conditions.
     *
     * @throws DcGeneralRuntimeException If the sub filter has a sub filter.
     * @throws DcGeneralRuntimeException If the sub filter has a no filter.
     */
    private static function calculateSubFilter(array $filter, QueryBuilder $queryBuilder): string
    {
        if (null !== ($rule = static::determineFilterAndOr($filter, $queryBuilder))) {
            return $rule;
        }

        if (null !== ($rule = static::determineFilterComparing($filter, $queryBuilder))) {
            return $rule;
        }

        if (null !== ($rule = static::determineFilterInOrNotInList($filter, $queryBuilder))) {
            return $rule;
        }

        if (null !== ($rule = static::determineFilterLikeOrNotLike($filter, $queryBuilder))) {
            return $rule;
        }

        if (null !== ($rule = static::determineFilterIsNullOrIsNotNull($filter, $queryBuilder))) {
            return $rule;
        }

        throw new DcGeneralRuntimeException('Error processing filter array ' . var_export($filter, true), 1);
    }

    /**
     * Determine sql filter for and/or.
     *
     * @param TSQLFilter   $filter       The query filter.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string|null
     */
    private static function determineFilterAndOr(array $filter, QueryBuilder $queryBuilder): ?string
    {
        if (!\in_array($filter['operation'], ['AND', 'OR'], true)) {
            return null;
        }
        /** @var TSQLFilterAND|TSQLFilterOR $filter */

        static::filterAndOr($filter, $queryBuilder);

        return '';
    }

    /**
     * Determine sql filter for comparing.
     *
     * @param TSQLFilter   $filter       The query filter.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string|null
     */
    private static function determineFilterComparing(array $filter, QueryBuilder $queryBuilder): ?string
    {
        if (!\in_array($filter['operation'], ['=', '>', '>=', '<', '<=', '<>'], true)) {
            return null;
        }
        /** @var TSQLFilterCMP $filter */

        return static::filterComparing($filter, $queryBuilder);
    }

    /**
     * Determine sql filter for in or not in list.
     *
     * @param TSQLFilter   $filter       The query filter.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string|null
     */
    private static function determineFilterInOrNotInList(array $filter, QueryBuilder $queryBuilder): ?string
    {
        if (!\in_array($filter['operation'], ['IN', 'NOT IN'])) {
            return null;
        }
        /** @var TSQLFilterIN $filter */

        return static::filterInOrNotInList($filter, $queryBuilder);
    }

    /**
     * Determine sql filter for like or not like.
     *
     * @param TSQLFilter   $filter       The query filter.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string|null
     */
    private static function determineFilterLikeOrNotLike(array $filter, QueryBuilder $queryBuilder)
    {
        if (!\in_array($filter['operation'], ['LIKE', 'NOT LIKE'])) {
            return null;
        }
        /** @var TSQLFilterLIKE $filter */

        return static::filterLikeOrNotLike($filter, $queryBuilder);
    }

    /**
     * Determine sql filter for is null or is not null.
     *
     * @param TSQLFilter   $filter       The query filter.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string|null
     */
    private static function determineFilterIsNullOrIsNotNull(array $filter, QueryBuilder $queryBuilder)
    {
        if (!\in_array($filter['operation'], ['IS NULL', 'IS NOT NULL'])) {
            return null;
        }
        /** @var TSQLFilterNULL $filter */

        return static::filterIsNullOrIsNotNull($filter, $queryBuilder);
    }

    /**
     * Build an AND or OR query.
     *
     * @param TSQLFilterAND|TSQLFilterOR $operation    The operation to convert.
     * @param QueryBuilder               $queryBuilder The query builder.
     *
     * @return void
     */
    private static function filterAndOr(array $operation, QueryBuilder $queryBuilder): void
    {
        $children = $operation['children'];

        if (empty($children)) {
            return;
        }

        $whereOperation = \strtolower($operation['operation']) . 'Where';

        /** @var TSQLFilter $child */
        foreach ($children as $child) {
            if ('' !== $child = static::calculateSubFilter($child, $queryBuilder)) {
                $queryBuilder->{$whereOperation}($child);
            }
        }
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param TSQLFilterCMP $operation    The operation to apply.
     * @param QueryBuilder  $queryBuilder The query builder.
     *
     * @return string
     */
    private static function filterComparing(array $operation, QueryBuilder $queryBuilder): string
    {
        return $queryBuilder
            ->expr()
            ->comparison(
                $operation['property'],
                $operation['operation'],
                $queryBuilder->createNamedParameter($operation['value'])
            );
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param TSQLFilterIN $operation    The operation to apply.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string
     */
    private static function filterInOrNotInList(array $operation, QueryBuilder $queryBuilder): string
    {
        $expressionMethod = \lcfirst(\preg_replace('/\s+/', '', \ucwords(\strtolower($operation['operation']))));

        $values = [];
        foreach ($operation['values'] as $index => $value) {
            $parameterName = $operation['property'] . '_in_' . $index;
            $values[]      = ':' . $parameterName;
            $queryBuilder->setParameter($parameterName, $value);
        }

        return $queryBuilder
            ->expr()
            ->{$expressionMethod}($operation['property'], $values);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param TSQLFilterLIKE $operation    The operation to apply.
     * @param QueryBuilder   $queryBuilder The query builder.
     *
     * @return string
     */
    private static function filterLikeOrNotLike(array $operation, QueryBuilder $queryBuilder): string
    {
        $wildcards = \str_replace(['*', '?'], ['%', '_'], $operation['value']);

        return $queryBuilder
            ->expr()
            ->comparison($operation['property'], $operation['operation'], '"' . $wildcards . '"');
    }

    /**
     * Return the filter query for a "foo IS NULL" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param TSQLFilterNULL $operation    The operation to apply.
     * @param QueryBuilder   $queryBuilder The query builder.
     *
     * @return string
     */
    private static function filterIsNullOrIsNotNull(array $operation, QueryBuilder $queryBuilder): string
    {
        $expressionMethod = \lcfirst(\preg_replace('/\s+/', '', \ucwords(\strtolower($operation['operation']))));

        return $queryBuilder
            ->expr()
            ->{$expressionMethod}($operation['property']);
    }
}
