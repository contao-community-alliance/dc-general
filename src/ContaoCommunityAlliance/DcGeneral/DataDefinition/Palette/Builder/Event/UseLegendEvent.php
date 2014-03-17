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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a legend is used.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class UseLegendEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.use-legend';

	/**
	 * The legend interface.
	 *
	 * @var LegendInterface
	 */
	protected $legend;

	/**
	 * Create a new instance.
	 *
	 * @param LegendInterface $legend         The legend being used.
	 *
	 * @param PaletteBuilder  $paletteBuilder The palette builder in use.
	 */
	public function __construct(LegendInterface $legend, PaletteBuilder $paletteBuilder)
	{
		$this->legend = $legend;
		parent::__construct($paletteBuilder);
	}

	/**
	 * Retrieve the legend.
	 *
	 * @return LegendInterface
	 */
	public function getLegend()
	{
		return $this->legend;
	}
}
