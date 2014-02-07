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
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

/**
 * This event gets emitted when a palette collection is finished.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class FinishPaletteCollectionEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.finish-palette-collection';

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
		$this->setPaletteCollection($paletteCollection);
		parent::__construct($paletteBuilder);
	}

	/**
	 * Set the palette collection.
	 *
	 * @param PaletteCollectionInterface $paletteCollection The palette collection.
	 *
	 * @return FinishPaletteCollectionEvent
	 */
	public function setPaletteCollection(PaletteCollectionInterface $paletteCollection)
	{
		$this->paletteCollection = $paletteCollection;

		return $this;
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
