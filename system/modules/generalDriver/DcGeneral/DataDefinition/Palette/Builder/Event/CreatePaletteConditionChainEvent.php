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
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class CreatePaletteConditionChainEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-plaette-condition-chain';

	/**
	 * @var PaletteConditionChain
	 */
	protected $paletteConditionChain;

	/**
	 * @param PaletteConditionChain $paletteConditionChain
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(PaletteConditionChain $paletteConditionChain, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteConditionChain($paletteConditionChain);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PaletteConditionChain $paletteConditionChain
	 */
	public function setPaletteConditionChain(PaletteConditionChain $paletteConditionChain)
	{
		$this->paletteConditionChain = $paletteConditionChain;
		return $this;
	}

	/**
	 * @return PaletteConditionChain
	 */
	public function getPaletteConditionChain()
	{
		return $this->paletteConditionChain;
	}

}
