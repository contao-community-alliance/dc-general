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
use DcGeneral\EnvironmentInterface;

class SetPaletteCollectionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-palette-collection-class-name';

	/**
	 * @var string
	 */
	protected $paletteCollectionClassName;

	/**
	 * @param string               $paletteCollectionClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($paletteCollectionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteCollectionClassName($paletteCollectionClassName);
		parent::__construct($paletteBuilder);
	}

	/**
	 * @param string $paletteCollectionClassName
	 */
	public function setPaletteCollectionClassName($paletteCollectionClassName)
	{
		$this->paletteCollectionClassName = (string) $paletteCollectionClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaletteCollectionClassName()
	{
		return $this->paletteCollectionClassName;
	}

}
