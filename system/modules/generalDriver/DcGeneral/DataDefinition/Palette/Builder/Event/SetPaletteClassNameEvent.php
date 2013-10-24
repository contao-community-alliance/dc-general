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

class SetPaletteClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetPaletteClassName';

	/**
	 * @var string
	 */
	protected $paletteClassName;

	/**
	 * @param string               $paletteClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($paletteClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteClassName($paletteClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $paletteClassName
	 */
	public function setPaletteClassName($paletteClassName)
	{
		$this->paletteClassName = (string) $paletteClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaletteClassName()
	{
		return $this->paletteClassName;
	}

}
