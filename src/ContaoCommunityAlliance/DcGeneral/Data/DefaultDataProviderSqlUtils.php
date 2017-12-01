<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Static helper class for DefaultDataProvider.
 */
class DefaultDataProviderSqlUtils
{
    /**
     * Build the field list.
     *
     * Returns all values from $objConfig->getFields() as comma separated list.
     *
     * @param ConfigInterface $config     The configuration to use.
     * @param string          $idProperty The name of the id property.
     *
     * @return string
     */
    public static function buildFieldQuery($config, $idProperty)
    {
        if ($config->getIdOnly()) {
            return $idProperty;
        }
        if (null !== $config->getFields()) {
            $fields = implode(', ', $config->getFields());
            if (stristr($fields, 'DISTINCT')) {
                return $fields;
            }
            return $idProperty . ', ' . $fields;
        }

        return '*';
    }

    /**
     * Build the WHERE clause for a configuration.
     *
     * @param ConfigInterface $config     The configuration to use.
     * @param array           $parameters The query parameters will get stored into this array.
     *
     * @return string  The combined WHERE clause (including the word "WHERE").
     */
    public static function buildWhereQuery($config, array &$parameters)
    {
        $query = static::buildFilterQuery($config, $parameters);
        if (empty($query)) {
            return '';
        }

        return ' WHERE ' . $query;
    }

    /**
     * Build the order by part of a query.
     *
     * @param ConfigInterface $config The configuration to use.
     *
     * @return string
     */
    public static function buildSortingQuery($config)
    {
        $sorting = $config->getSorting();
        $result  = '';
        $fields  = [];

        if (empty($sorting) || !is_array($sorting)) {
            return '';
        }
        foreach ($sorting as $field => $direction) {
            // array could be a simple field list or list of field => direction combinations.
            if (!empty($direction)) {
                $direction = strtoupper($direction);
                if (!in_array($direction, [DCGE::MODEL_SORTING_ASC, DCGE::MODEL_SORTING_DESC])) {
                    $field     = $direction;
                    $direction = DCGE::MODEL_SORTING_ASC;
                }
            } else {
                $direction = DCGE::MODEL_SORTING_ASC;
            }

            $fields[] = $field . ' ' . $direction;
        }

        $result .= ' ORDER BY ' . implode(', ', $fields);

        return $result;
    }

    /**
     * Build the WHERE conditions via calculateSubfilter().
     *
     * @param ConfigInterface $config     The configuration to use.
     *
     * @param array           $parameters The query parameters will get stored into this array.
     *
     * @return string The combined conditions.
     */
    private static function buildFilterQuery($config, array &$parameters)
    {
        $result = static::calculateSubfilter(
            ['operation' => 'AND', 'children'  => $config->getFilter()],
            $parameters
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
     * @param array $filter     The filter to be combined to a valid SQL filter query.
     * @param array $parameters The query parameters will get stored into this array.
     *
     * @return string The combined WHERE conditions.
     *
     * @throws DcGeneralRuntimeException If an invalid filter entry is encountered.
     */
    private static function calculateSubfilter($filter, array &$parameters)
    {
        if (!is_array($filter)) {
            throw new DcGeneralRuntimeException('Error Processing sub filter: ' . var_export($filter, true), 1);
        }

        switch ($filter['operation']) {
            case 'AND':
            case 'OR':
                return static::filterAndOr($filter, $parameters);

            case '=':
            case '>':
            case '<':
                return static::filterComparing($filter, $parameters);

            case 'IN':
                return static::filterInList($filter, $parameters);

            case 'LIKE':
                return static::filterLike($filter, $parameters);

            default:
        }

        throw new DcGeneralRuntimeException('Error processing filter array ' . var_export($filter, true), 1);
    }

    /**
     * Build an AND or OR query.
     *
     * @param array $operation The operation to convert.
     * @param array $params    The parameter array for the resulting query.
     *
     * @return string
     */
    private static function filterAndOr($operation, &$params)
    {
        $children = $operation['children'];

        if (empty($children)) {
            return '';
        }

        $combine = [];
        foreach ($children as $child) {
            $combine[] = static::calculateSubfilter($child, $params);
        }

        return implode(sprintf(' %s ', $operation['operation']), $combine);
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param array $operation The operation to apply.
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    private static function filterComparing($operation, &$params)
    {
        $params[] = $operation['value'];

        return sprintf('(%s %s ?)', $operation['property'], $operation['operation']);
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param array $operation The operation to apply.
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    private static function filterInList($operation, &$params)
    {
        $params    = array_merge($params, array_values($operation['values']));
        $wildcards = rtrim(str_repeat('?,', count($operation['values'])), ',');

        return sprintf('(%s IN (%s))', $operation['property'], $wildcards);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param array $operation The operation to apply.
     *
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    private static function filterLike($operation, &$params)
    {
        $wildcards = str_replace(array('*', '?'), array('%', '_'), $operation['value']);
        $params[]  = $wildcards;

        return sprintf('(%s LIKE ?)', $operation['property'], $wildcards);
    }
}
