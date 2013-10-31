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

namespace DcGeneral\DataDefinition\Palette\Builder\Event;

use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition as PalettePropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class FinishConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.finish-condition';

	/**
	 * @var PaletteConditionInterface|PropertyConditionInterface
	 */
	protected $condition;

	/**
	 * @param PaletteConditionInterface|PropertyConditionInterface $condition
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($condition, PaletteBuilder $paletteBuilder)
	{
		$this->setCondition($condition);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PalettePropertyValueCondition|PropertyValueCondition $condition
	 */
	public function setCondition($condition)
	{
		if (!$condition instanceof PaletteConditionInterface and !$condition instanceof PropertyConditionInterface) {
			throw new DcGeneralInvalidArgumentException();
		}

		$this->condition = $condition;
		return $this;
	}

	/**
	 * @return PalettePropertyValueCondition|PropertyValueCondition
	 */
	public function getCondition()
	{
		return $this->condition;
	}

}
