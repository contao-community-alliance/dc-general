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

use DcGeneral\DataDefinition\Palette\LegendInterface;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\EnvironmentInterface;

class FinishLegendEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\FinishLegend';

	/**
	 * @var LegendInterface
	 */
	protected $legend;

	/**
	 * @param LegendInterface $legend
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(LegendInterface $legend, PaletteBuilder $paletteBuilder)
	{
		$this->setLegend($legend);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param LegendInterface $legend
	 */
	public function setLegend(LegendInterface $legend)
	{
		$this->legend = $legend;
		return $this;
	}

	/**
	 * @return LegendInterface
	 */
	public function getLegend()
	{
		return $this->legend;
	}

}
