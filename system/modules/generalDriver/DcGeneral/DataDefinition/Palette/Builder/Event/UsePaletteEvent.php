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
use DcGeneral\DataDefinition\Palette\PaletteInterface;
use DcGeneral\EnvironmentInterface;

class UsePaletteEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-palette';

	/**
	 * @var PaletteInterface
	 */
	protected $palette;

	/**
	 * @param PaletteInterface $palette
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(PaletteInterface $palette, PaletteBuilder $paletteBuilder)
	{
		$this->palette = $palette;
		parent::__construct($paletteBuilder);
	}

	/**
	 * @return PaletteInterface
	 */
	public function getPalette()
	{
		return $this->palette;
	}
}
