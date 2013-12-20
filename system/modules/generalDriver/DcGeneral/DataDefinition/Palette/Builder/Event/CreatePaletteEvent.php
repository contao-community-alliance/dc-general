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

/**
 * This event is emitted when a new palette has been created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreatePaletteEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.create-palette';

	/**
	 * The palette that has been created.
	 *
	 * @var PaletteInterface
	 */
	protected $palette;

	/**
	 * Create a new instance.
	 *
	 * @param PaletteInterface $palette        The palette that has been created.
	 *
	 * @param PaletteBuilder   $paletteBuilder The palette builder in use.
	 */
	public function __construct(PaletteInterface $palette, PaletteBuilder $paletteBuilder)
	{
		$this->setPalette($palette);
		parent::__construct($paletteBuilder);
	}

	/**
	 * Set the palette.
	 *
	 * @param PaletteInterface $palette The palette.
	 *
	 * @return CreatePaletteEvent
	 */
	public function setPalette(PaletteInterface $palette)
	{
		$this->palette = $palette;

		return $this;
	}

	/**
	 * Retrieve the palette.
	 *
	 * @return PaletteInterface
	 */
	public function getPalette()
	{
		return $this->palette;
	}
}
