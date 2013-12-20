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

/**
 * This event gets emitted when the class name of the default palette condition is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetDefaultPaletteConditionClassNameEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.set-default-palette-condition-class-name';

	/**
	 * The class name.
	 *
	 * @var string
	 */
	protected $defaultPaletteConditionClassName;

	/**
	 * Create a new instance.
	 *
	 * @param string         $defaultPaletteConditionClassName The class name.
	 *
	 * @param PaletteBuilder $paletteBuilder                   The palette builder in use.
	 */
	public function __construct($defaultPaletteConditionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setDefaultPaletteConditionClassName($defaultPaletteConditionClassName);
		parent::__construct($paletteBuilder);
	}

	/**
	 * Set the class name.
	 *
	 * @param string $defaultPaletteConditionClassName The class name.
	 *
	 * @return SetDefaultPaletteConditionClassNameEvent
	 */
	public function setDefaultPaletteConditionClassName($defaultPaletteConditionClassName)
	{
		$this->defaultPaletteConditionClassName = (string)$defaultPaletteConditionClassName;
		return $this;
	}

	/**
	 * Retrieve the class name.
	 *
	 * @return string
	 */
	public function getDefaultPaletteConditionClassName()
	{
		return $this->defaultPaletteConditionClassName;
	}
}
