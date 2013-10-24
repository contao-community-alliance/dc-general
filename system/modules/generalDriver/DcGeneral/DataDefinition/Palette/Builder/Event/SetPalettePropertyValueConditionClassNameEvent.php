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

use DcGeneral\DataDefinition\Palette\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class SetPalettePropertyValueConditionClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetPalettePropertyValueConditionClassName';

	/**
	 * @var string
	 */
	protected $palettePropertyValueConditionClassName;

	/**
	 * @param string               $palettePropertyValueConditionClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($palettePropertyValueConditionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $palettePropertyValueConditionClassName
	 */
	public function setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName)
	{
		$this->palettePropertyValueConditionClassName = (string) $palettePropertyValueConditionClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPalettePropertyValueConditionClassName()
	{
		return $this->palettePropertyValueConditionClassName;
	}

}
