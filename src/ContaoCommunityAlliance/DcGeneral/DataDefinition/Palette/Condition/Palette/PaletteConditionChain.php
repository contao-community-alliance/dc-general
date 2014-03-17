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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\AbstractConditionChain;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A chain of palette conditions.
 */
class PaletteConditionChain extends AbstractConditionChain implements PaletteConditionInterface
{
	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException When an condition that does not implement PaletteConditionInterface
	 *                                   is encountered.
	 */
	public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$totalCount = false;

		foreach ($this->conditions as $condition)
		{
			if (!($condition instanceof PaletteConditionInterface))
			{
				throw new DcGeneralRuntimeException('Invalid condition in chain: '. get_class($condition));
			}

			$conditionCount = $condition->getMatchCount($model, $input);

			if ($conditionCount !== false)
			{
				$totalCount += $conditionCount;
			}
			elseif ($this->conjunction == static::AND_CONJUNCTION)
			{
				return false;
			}
		}

		return $totalCount;
	}
}
