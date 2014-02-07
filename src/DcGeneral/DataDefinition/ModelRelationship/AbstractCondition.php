<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\ModelRelationship;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class is an abstract base for defining model relationship conditions.
 *
 * It implements a basic condition check.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
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
		foreach ($filter['children'] as $child)
		{
			// AND => first false means false.
			if (!self::checkCondition($model, $child))
			{
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
		foreach ($filter['children'] as $child)
		{
			// OR => first true means true.
			if (self::checkCondition($model, $child))
			{
				return true;
			}
		}
		return false;
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
		switch ($arrFilter['operation'])
		{
			case 'AND':
			case 'OR':
				// FIXME: backwards compat - remove when done.
				if (is_array($arrFilter['childs']))
				{
					trigger_error('Filter array uses deprecated entry "childs", please use "children" instead.', E_USER_DEPRECATED);
					$arrFilter['children'] = $arrFilter['childs'];
				}

				if ($arrFilter['operation'] == 'AND')
				{
					return self::checkAndFilter($objParentModel, $arrFilter['children']);
				}
				else
				{
					return self::checkOrFilter($objParentModel, $arrFilter['children']);
				}
				break;

			case '=':
				return ($objParentModel->getProperty($arrFilter['property']) == $arrFilter['value']);

			case '>':
				return ($objParentModel->getProperty($arrFilter['property']) > $arrFilter['value']);

			case '<':
				return ($objParentModel->getProperty($arrFilter['property']) < $arrFilter['value']);

			case 'IN':
				return in_array($objParentModel->getProperty($arrFilter['property']), $arrFilter['value']);

			default:
		}

		throw new DcGeneralRuntimeException(
			'Error processing filter array - unknown operation ' . var_export($arrFilter, true),
			1
		);
	}
}
