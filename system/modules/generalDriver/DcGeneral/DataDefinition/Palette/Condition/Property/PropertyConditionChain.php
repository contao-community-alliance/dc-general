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

namespace DcGeneral\DataDefinition\Palette\Condition\Property;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\AbstractConditionChain;

/**
 * A chain of property conditions.
 */
class PropertyConditionChain extends AbstractConditionChain implements PropertyConditionInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($this->conjunction == static::AND_CONJUNCTION)
		{
			foreach ($this->conditions as $condition)
			{
				if (!$condition->match($model, $input))
				{
					return false;
				}
			}

			return true;
		}

		foreach ($this->conditions as $condition)
		{
			if ($condition->match($model, $input))
			{
				return true;
			}
		}

		return false;
	}
}
