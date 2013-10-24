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

class SetPaletteConditionChainClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteConditionChainClassName';

	/**
	 * @var string
	 */
	protected $paletteConditionChainClassName;

	/**
	 * @param string               $paletteConditionChainClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($paletteConditionChainClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteConditionChainClassName($paletteConditionChainClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $paletteConditionChainClassName
	 */
	public function setPaletteConditionChainClassName($paletteConditionChainClassName)
	{
		$this->paletteConditionChainClassName = (string) $paletteConditionChainClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaletteConditionChainClassName()
	{
		return $this->paletteConditionChainClassName;
	}

}
