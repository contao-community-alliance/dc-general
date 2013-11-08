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
use DcGeneral\DataDefinition\ConditionInterface;

interface WeightAwarePaletteConditionInterface extends PaletteConditionInterface
{
	/**
	 * Set the weight of this condition.
	 *
	 * @param int $weight
	 *
	 * @return WeightAwarePaletteConditionInterface
	 */
	public function setWeight($weight);

	/**
	 * Get the weight of this condition.
	 *
	 * @return int
	 */
	public function getWeight();
}
