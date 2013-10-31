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

use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class SetPropertyConditionChainClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-property-condition-chain-class-name';

	/**
	 * @var string
	 */
	protected $palettePropertyConditionChainClassName;

	/**
	 * @param string               $palettePropertyConditionChainClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($palettePropertyConditionChainClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPalettePropertyConditionChainClassName($palettePropertyConditionChainClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $palettePropertyConditionChainClassName
	 */
	public function setPalettePropertyConditionChainClassName($palettePropertyConditionChainClassName)
	{
		$this->palettePropertyConditionChainClassName = (string) $palettePropertyConditionChainClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPalettePropertyConditionChainClassName()
	{
		return $this->palettePropertyConditionChainClassName;
	}

}
