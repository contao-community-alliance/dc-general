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

use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition as PalettePropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class CreatePropertyValueConditionEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\CreatePropertyValueCondition';

	/**
	 * @var PalettePropertyValueCondition|PropertyValueCondition
	 */
	protected $propertyValueCondition;

	/**
	 * @param PalettePropertyValueCondition|PropertyValueCondition $propertyValueCondition
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($propertyValueCondition, PaletteBuilder $paletteBuilder)
	{
		$this->setPropertyValueCondition($propertyValueCondition);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PalettePropertyValueCondition|PropertyValueCondition $propertyValueCondition
	 */
	public function setPropertyValueCondition($propertyValueCondition)
	{
		if (!$propertyValueCondition instanceof PalettePropertyValueCondition and !$propertyValueCondition instanceof PropertyValueCondition) {
			throw new DcGeneralInvalidArgumentException();
		}

		$this->propertyValueCondition = $propertyValueCondition;
		return $this;
	}

	/**
	 * @return PalettePropertyValueCondition|PropertyValueCondition
	 */
	public function getPropertyValueCondition()
	{
		return $this->propertyValueCondition;
	}

}
