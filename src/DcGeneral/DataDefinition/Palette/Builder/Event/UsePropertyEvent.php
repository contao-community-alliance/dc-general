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
use DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This event is emitted when a property is used.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class UsePropertyEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.use-property';

	/**
	 * The property.
	 *
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * Create a new instance.
	 *
	 * @param PropertyInterface $property       The property.
	 *
	 * @param PaletteBuilder    $paletteBuilder The palette builder in use.
	 */
	public function __construct(PropertyInterface $property, PaletteBuilder $paletteBuilder)
	{
		$this->property = $property;
		parent::__construct($paletteBuilder);
	}

	/**
	 * Retrieve the property.
	 *
	 * @return PropertyInterface
	 */
	public function getProperty()
	{
		return $this->property;
	}
}
