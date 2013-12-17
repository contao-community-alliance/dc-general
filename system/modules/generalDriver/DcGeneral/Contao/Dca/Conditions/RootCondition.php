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

namespace DcGeneral\Contao\Dca\Conditions;

use DcGeneral\DataDefinition\RootConditionInterface;
use DcGeneral\DataDefinition\AbstractCondition;
use DcGeneral\Data\ModelInterface;

class RootCondition
	extends AbstractCondition
	implements RootConditionInterface
{
	/**
	 * The Container instance to which this condition belongs to.
	 *
	 * @var \DcGeneral\Contao\Dca\Container
	 */
	protected $objParent;

	/**
	 * The name of the table this condition is being applied to.
	 *
	 * @var string
	 */
	protected $strTable;
	
	protected $setOn;

	public function __construct($objParent, $strTable)
	{
		$this->objParent = $objParent;
		$this->strTable  = $strTable;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilter()
	{
		$arrData = $this->get('filter');

		if($arrData == null)
		{
			$arrData = $this->objParent->getFromDca(sprintf('dca_config/rootEntries/%s/%s', 'self', 'filter'));
		}

		return $arrData;
	}

	public function setFilterArray($value)
	{
		$this->filter = $value;

		return $this;
	}

	public function getFilterArray()
	{
		return $this->filter;
	}

	public function setSetters($value)
	{
		$this->setOn = $value;
		return $this;
	}

	public function getSetters()
	{
		return $this->setOn;
	}

	/**
	 * Apply a condition to a model.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return void
	 */
	public function applyTo($objModel)
	{
		if ($this->setOn)
		{
			foreach ($this->setOn as $rule)
			{
				$objModel->setProperty($rule['property'], $rule['value']);
			}
		}

		return $this;
	}

	/**
	 * Test if the given model is indeed a root object for this condition.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return bool
	 */
	public function matches($objModel)
	{
		if ($this->getFilterArray())
		{
			return $this->checkCondition($objModel, $this->getFilterArray());
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('dca_config/rootEntries/%s/%s', $this->strTable, $strKey));
	}

	
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
