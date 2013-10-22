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

namespace DcGeneral\DataDefinition;

use DcGeneral\DataDefinition\ConditionInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

abstract class AbstractCondition implements ConditionInterface
{
	public static function checkCondition(ModelInterface $objParentModel, $arrFilter)
	{
		switch ($arrFilter['operation'])
		{
			case 'AND':
			case 'OR':
				// FIXME: backwards compat - remove when done
				if (is_array($arrFilter['childs']))
				{
					trigger_error('Filter array uses deprecated entry "childs", please use "children" instead.', E_USER_DEPRECATED);
					$arrFilter['children'] = $arrFilter['childs'];
				}
				// End of b.c. code.

				if ($arrFilter['operation'] == 'AND')
				{
					foreach ($arrFilter['children'] as $arrChild)
					{
						// AND => first false means false
						if (!self::checkCondition($objParentModel, $arrChild))
						{
							return false;
						}
					}
					return true;
				}
				else
				{
					foreach ($arrFilter['children'] as $arrChild)
					{
						// OR => first true means true
						if (self::checkCondition($objParentModel, $arrChild))
						{
							return true;
						}
					}
					return false;
				}
				break;

			case '=':
				return ($objParentModel->getProperty($arrFilter['property']) == $arrFilter['value']);
				break;
			case '>':
				return ($objParentModel->getProperty($arrFilter['property']) > $arrFilter['value']);
				break;
			case '<':
				return ($objParentModel->getProperty($arrFilter['property']) < $arrFilter['value']);
				break;

			case 'IN':
				return in_array($objParentModel->getProperty($arrFilter['property']), $arrFilter['value']);
				break;

			default:
				throw new DcGeneralRuntimeException('Error processing filter array - unknown operation ' . var_export($arrFilter, true), 1);
		}
	}
}
