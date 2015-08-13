<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class is an abstract base for defining model relationship conditions.
 *
 * It implements a basic condition check.
 */
abstract class AbstractCondition
{
    /**
     * Check if an AND condition filter matches.
     *
     * @param ModelInterface $model  The model to check the condition against.
     *
     * @param array          $filter The filter rules to be applied.
     *
     * @return bool
     */
    protected static function checkAndFilter($model, $filter)
    {
        foreach ($filter as $child) {
            // AND => first false means false.
            if (!self::checkCondition($model, $child)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if an AND condition filter matches.
     *
     * @param ModelInterface $model  The model to check the condition against.
     *
     * @param array          $filter The filter rules to be applied.
     *
     * @return bool
     */
    protected static function checkOrFilter($model, $filter)
    {
        foreach ($filter as $child) {
            // OR => first true means true.
            if (self::checkCondition($model, $child)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract a condition value depending if it is a remote value or property.
     *
     * @param array          $condition The condition array.
     *
     * @param ModelInterface $parent    The parent model.
     *
     * @return mixed
     */
    protected static function getConditionValue($condition, $parent)
    {
        if (isset($condition['remote_value'])) {
            return $condition['remote_value'];
        }

        return $parent->getProperty($condition['property']);
    }

    /**
     * Check if the passed filter rules apply to the given model.
     *
     * @param ModelInterface $objParentModel The model to check the condition against.
     *
     * @param array          $arrFilter      The condition filter to be applied.
     *
     * @return bool
     *
     * @throws DcGeneralRuntimeException When an unknown filter operation is encountered.
     */
    public static function checkCondition(ModelInterface $objParentModel, $arrFilter)
    {
        switch ($arrFilter['operation']) {
            case 'AND':
                return self::checkAndFilter($objParentModel, $arrFilter['children']);

            case 'OR':
                return self::checkOrFilter($objParentModel, $arrFilter['children']);

            case '=':
                return (self::getConditionValue($arrFilter, $objParentModel) == $arrFilter['value']);

            case '>':
                return (self::getConditionValue($arrFilter, $objParentModel) > $arrFilter['value']);

            case '<':
                return (self::getConditionValue($arrFilter, $objParentModel) < $arrFilter['value']);

            case 'IN':
                return in_array($objParentModel->getProperty($arrFilter['property']), $arrFilter['value']);

            case 'LIKE':
                throw new DcGeneralRuntimeException('LIKE unsupported as of now.');

            default:
        }

        throw new DcGeneralRuntimeException(
            'Error processing filter array - unknown operation ' . var_export($arrFilter, true),
            1
        );
    }
}
