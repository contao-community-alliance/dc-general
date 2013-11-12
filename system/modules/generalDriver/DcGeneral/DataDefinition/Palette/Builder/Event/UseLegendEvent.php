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

class UseLegendEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-legend';

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
		$this->legend = $legend;
		parent::__construct($paletteBuilder);
	}

	/**
	 * @return LegendInterface
	 */
	public function getLegend()
	{
		return $this->legend;
	}
}
