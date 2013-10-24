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
use DcGeneral\DataDefinition\Palette\PropertyInterface;
use DcGeneral\EnvironmentInterface;

class CreatePropertyEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\CreateProperty';

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
		$this->setProperty($property);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param PropertyInterface $property
	 */
	public function setProperty(PropertyInterface $property)
	{
		$this->property = $property;
		return $this;
	}

	/**
	 * @return PropertyInterface
	 */
	public function getProperty()
	{
		return $this->property;
	}

}
