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

use DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class CreateDefaultPaletteConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-default-palette-condition';

	/**
	 * @var DefaultPaletteCondition
	 */
	protected $defaultPaletteCondition;

	/**
	 * @param DefaultPaletteCondition $defaultPaletteCondition
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(DefaultPaletteCondition $defaultPaletteCondition, PaletteBuilder $paletteBuilder)
	{
		$this->setDefaultPaletteCondition($defaultPaletteCondition);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param DefaultPaletteCondition $defaultPaletteCondition
	 */
	public function setDefaultPaletteCondition(DefaultPaletteCondition $defaultPaletteCondition)
	{
		$this->defaultPaletteCondition = $defaultPaletteCondition;
		return $this;
	}

	/**
	 * @return DefaultPaletteCondition
	 */
	public function getDefaultPaletteCondition()
	{
		return $this->defaultPaletteCondition;
	}

}
