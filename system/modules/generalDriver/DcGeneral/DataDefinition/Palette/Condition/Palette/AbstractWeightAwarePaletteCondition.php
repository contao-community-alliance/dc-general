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

abstract class AbstractWeightAwarePaletteCondition implements WeightAwarePaletteConditionInterface
{
	/**
	 * The weight of this condition.
	 *
	 * @var int
	 */
	protected $weight = 1;

	/**
	 * {@inheritdoc}
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getWeight()
	{
		return $this->weight;
	}
}
