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
use DcGeneral\EnvironmentInterface;

class UsePropertyEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.use-property';

	/**
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * @param PropertyInterface $property
	 * @param PaletteBuilder $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct(PropertyInterface $property, PaletteBuilder $paletteBuilder)
	{
		$this->property = $property;
		parent::__construct($paletteBuilder);
	}

	/**
	 * @return PropertyInterface
	 */
	public function getProperty()
	{
		return $this->property;
	}
}
