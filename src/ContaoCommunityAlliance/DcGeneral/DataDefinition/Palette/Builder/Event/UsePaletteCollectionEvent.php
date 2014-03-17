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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

/**
 * This event gets emitted when a palette collection gets used.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class UsePaletteCollectionEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.use-palette-collection';

	/**
	 * The palette collection.
	 *
	 * @var PaletteCollectionInterface
	 */
	protected $paletteCollection;

	/**
	 * Create a new instance.
	 *
	 * @param PaletteCollectionInterface $paletteCollection The palette collection.
	 *
	 * @param PaletteBuilder             $paletteBuilder    The palette builder in use.
	 */
	public function __construct(PaletteCollectionInterface $paletteCollection, PaletteBuilder $paletteBuilder)
	{
		$this->paletteCollection = $paletteCollection;
		parent::__construct($paletteBuilder);
	}

	/**
	 * Retrieve the palette collection.
	 *
	 * @return PaletteCollectionInterface
	 */
	public function getPaletteCollection()
	{
		return $this->paletteCollection;
	}
}
