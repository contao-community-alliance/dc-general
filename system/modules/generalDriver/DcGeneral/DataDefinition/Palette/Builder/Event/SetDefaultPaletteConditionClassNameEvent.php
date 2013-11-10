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

class SetDefaultPaletteConditionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-default-palette-condition-class-name';

	/**
	 * @var string
	 */
	protected $defaultPaletteConditionClassName;

	/**
	 * @param string               $defaultPaletteConditionClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($defaultPaletteConditionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setDefaultPaletteConditionClassName($defaultPaletteConditionClassName);
		parent::__construct($paletteBuilder);
	}

	/**
	 * @param string $defaultPaletteConditionClassName
	 */
	public function setDefaultPaletteConditionClassName($defaultPaletteConditionClassName)
	{
		$this->defaultPaletteConditionClassName = (string) $defaultPaletteConditionClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultPaletteConditionClassName()
	{
		return $this->defaultPaletteConditionClassName;
	}

}
