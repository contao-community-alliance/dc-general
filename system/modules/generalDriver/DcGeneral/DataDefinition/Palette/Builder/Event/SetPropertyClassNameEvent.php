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
use DcGeneral\EnvironmentInterface;

class SetPropertyClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyClassName';

	/**
	 * @var string
	 */
	protected $propertyClassName;

	/**
	 * @param string               $propertyClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($propertyClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPropertyClassName($propertyClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $propertyClassName
	 */
	public function setPropertyClassName($propertyClassName)
	{
		$this->propertyClassName = (string) $propertyClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPropertyClassName()
	{
		return $this->propertyClassName;
	}

}
