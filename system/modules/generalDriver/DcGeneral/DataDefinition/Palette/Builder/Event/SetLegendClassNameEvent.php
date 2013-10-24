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

class SetLegendClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetLegendClassName';

	/**
	 * @var string
	 */
	protected $legendClassName;

	/**
	 * @param string               $legendClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($legendClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setLegendClassName($legendClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $legendClassName
	 */
	public function setLegendClassName($legendClassName)
	{
		$this->legendClassName = (string) $legendClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLegendClassName()
	{
		return $this->legendClassName;
	}

}
