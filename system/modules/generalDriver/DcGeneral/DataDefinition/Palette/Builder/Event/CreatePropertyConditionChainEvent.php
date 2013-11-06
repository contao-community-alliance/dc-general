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

use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class CreatePropertyConditionChainEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-property-condition-chain';

	/**
	 * @var PropertyConditionChain
	 */
	protected $propertyConditionChain;

	/**
	 * @param PropertyConditionChain $propertyConditionChain
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(PropertyConditionChain $propertyConditionChain, PaletteBuilder $paletteBuilder)
	{
		$this->setPropertyConditionChain($propertyConditionChain);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PropertyConditionChain $propertyConditionChain
	 */
	public function setPropertyConditionChain(PropertyConditionChain $propertyConditionChain)
	{
		$this->propertyConditionChain = $propertyConditionChain;
		return $this;
	}

	/**
	 * @return PropertyConditionChain
	 */
	public function getPropertyConditionChain()
	{
		return $this->propertyConditionChain;
	}

}
