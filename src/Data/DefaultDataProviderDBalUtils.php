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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Static helper class for DefaultDataProvider.
 */
class DefaultDataProviderDBalUtils
{
    /**
     * Add the field list.
     *
     * Returns all values from $objConfig->getFields() as comma separated list.
     *
     * @param ConfigInterface $config       The configuration to use.
     *
     * @param string          $idProperty   The name of the id property.
     *
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

        if (null !== $config->getFields()) {
            $fields = implode(', ', $config->getFields());

            if (false !== stripos($fields, 'DISTINCT')) {
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
     *
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return void
     * @internal param array $parameters The query parameters will get stored into this array.
     *
     */
    public static function addWhere($config, QueryBuilder $queryBuilder)
    {
        self::addFilter($config, $queryBuilder);
    }

    /**
     * Add the order by part of a query.
     *
     * @param ConfigInterface $config       The configuration to use.
     *
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return void
     */
    public static function addSorting(ConfigInterface $config, QueryBuilder $queryBuilder)
    {
        if (empty($config->getSorting()) || !is_array($config->getSorting())) {
            return;
        }

        foreach ($config->getSorting() as $sort => $order) {
            // array could be a simple field list or list of field => direction combinations.
            if (!empty($order)) {
                $order = strtoupper($order);
                if (!in_array($order, [DCGE::MODEL_SORTING_ASC, DCGE::MODEL_SORTING_DESC])) {
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
     *
     * @param QueryBuilder    $queryBuilder The query builder.
     *
     * @return string The combined conditions.
     * @internal param array $parameters The query parameters will get stored into this array.
     *
     */
    private static function addFilter($config, QueryBuilder $queryBuilder)
    {
        $result = static::calculateSubFilter(
            ['operation' => 'AND', 'children' => $config->getFilter()],
            $queryBuilder
        );

        // Combine filter syntax.
        return $result ?: '';
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
     * =
     *                'property'           string (the name of a property)
     *                'value'              literal
     * >
     *                'property'           string (the name of a property)
     *                'value'              literal
     * <
     *                'property'           string (the name of a property)
     *                'value'              literal
     * IN
     *                'property'           string (the name of a property)
     *                'values'             array of literal
     *
     * LIKE
     *                'property'           string (the name of a property)
     *                'value'              literal - Wildcards * (Many) ? (One)
     *
     * @param array        $filter       The filter to be combined to a valid SQL filter query.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string The combined WHERE conditions.
     *
     * @internal param array $parameters The query parameters will get stored into this array.
     *
     */
    private static function calculateSubFilter($filter, QueryBuilder $queryBuilder)
    {
        if (!is_array($filter)) {
            throw new DcGeneralRuntimeException('Error Processing sub filter: ' . var_export($filter, true), 1);
        }

        switch ($filter['operation']) {
            case 'AND':
            case 'OR':
                static::filterAndOr($filter, $queryBuilder);

                return '';
            case '=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case '<>':
                return static::filterComparing($filter, $queryBuilder);

            case 'IN':
            case 'NOT IN':
                return static::filterInOrNotInList($filter, $queryBuilder);

            case 'LIKE':
            case 'NOT LIKE':
                return static::filterLikeOrNotLike($filter, $queryBuilder);

            case 'IS NULL':
            case 'IS NOT NULL':
                return static::filterIsNullOrIsNotNull($filter, $queryBuilder);

            default:
        }

        throw new DcGeneralRuntimeException('Error processing filter array ' . var_export($filter, true), 1);
    }

    /**
     * Build an AND or OR query.
     *
     * @param array        $operation    The operation to convert.
     * @param QueryBuilder $queryBuilder The query builder
     *
     * @internal param array $params The parameter array for the resulting query.
     *
     */
    private static function filterAndOr($operation, QueryBuilder $queryBuilder)
    {
        $children = $operation['children'];

        if (empty($children)) {
            return;
        }

        $whereOperation = strtolower($operation['operation']) . 'Where';

        foreach ($children as $child) {
            $queryBuilder->{$whereOperation}(static::calculateSubFilter($child, $queryBuilder));
        }
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param array        $operation    The operation to apply.
     *
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string
     * @internal param array $params The parameters of the entire query.
     *
     */
    private static function filterComparing($operation, QueryBuilder $queryBuilder)
    {
        $queryBuilder->setParameter(':' . $operation['property'], $operation['value']);

        return $queryBuilder
            ->expr()
            ->comparison($operation['property'], $operation['operation'], ':' . $operation['property']);
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param array        $operation    The operation to apply.
     * @param QueryBuilder $queryBuilder The query builder.
     *
     * @return string
     * @internal param array $params The parameters of the entire query.
     *
     */
    private static function filterInOrNotInList($operation, QueryBuilder $queryBuilder)
    {
        $expressionMethod = lcfirst(preg_replace('/\s+/', '', ucwords(strtolower($operation['operation']))));

        return $queryBuilder
            ->expr()
            ->{$expressionMethod}($operation['property'], $operation['values']);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param array        $operation The operation to apply.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     * @internal param array $params The parameters of the entire query.
     *
     */
    private static function filterLikeOrNotLike($operation, QueryBuilder $queryBuilder)
    {
        $wildcards = str_replace(array('*', '?'), array('%', '_'), $operation['value']);

        return $queryBuilder
            ->expr()
            ->comparison($operation['property'], $operation['operation'], '"' . $wildcards . '"');
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param array        $operation The operation to apply.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     * @internal param array $params The parameters of the entire query.
     *
     */
    private static function filterIsNullOrIsNotNull($operation, QueryBuilder $queryBuilder)
    {
        $expressionMethod = lcfirst(preg_replace('/\s+/', '', ucwords(strtolower($operation['operation']))));

        return $queryBuilder
            ->expr()
            ->{$expressionMethod}($operation['property']);
    }
}
