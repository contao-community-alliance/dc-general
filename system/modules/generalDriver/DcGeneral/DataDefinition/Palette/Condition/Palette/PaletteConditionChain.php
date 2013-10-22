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

namespace DcGeneral\DataDefinition\Palette\Condition\Palette;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\AbstractConditionChain;

/**
 * A condition define when a property is visible or editable and when not.
 */
class PaletteConditionChain extends AbstractConditionChain implements PaletteConditionInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$totalCount = false;
		
		foreach ($this->conditions as $condition)
		{
			$conditionCount = $condition->getMatchCount($model, $input);

			if ($conditionCount !== false) {
				$totalCount += $conditionCount;
			}
			else if ($this->conjunction == static::AND_CONJUNCTION) {
				return false;
			}
		}
		
		return $totalCount;
	}
}
