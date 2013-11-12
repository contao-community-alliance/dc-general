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
use DcGeneral\DataDefinition\Palette\PaletteInterface;
use DcGeneral\DataDefinition\Palette\PropertyInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class AddConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.add-condition';

	/**
	 * @var PaletteConditionInterface|PropertyConditionInterface
	 */
	protected $condition;

	/**
	 * @var PaletteInterface|PropertyInterface
	 */
	protected $target;

	/**
	 * @param PaletteConditionInterface|PropertyConditionInterface $condition
	 * @param PaletteInterface|PropertyInterface $target
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($condition, $target, PaletteBuilder $paletteBuilder)
	{
		$this->setCondition($condition);
		$this->setTarget($target);
		parent::__construct($paletteBuilder);
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

	/**
	 * @param PaletteInterface|PropertyInterface $target
	 */
	public function setTarget($target)
	{
		if (!$target instanceof PaletteInterface and !$target instanceof PropertyInterface) {
			throw new DcGeneralInvalidArgumentException();
		}

		$this->target = $target;
		return $this;
	}

	/**
	 * @return PaletteInterface|PropertyInterface
	 */
	public function getTarget()
	{
		return $this->target;
	}
}
