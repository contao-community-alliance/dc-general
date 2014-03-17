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

/**
 * This event gets emitted when a palette property value condition class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPalettePropertyValueConditionClassNameEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.set-palette-property-value-condition-class-name';

	/**
	 * The class name.
	 *
	 * @var string
	 */
	protected $palettePropertyValueConditionClassName;

	/**
	 * Create a new instance.
	 *
	 * @param string         $palettePropertyValueConditionClassName The class name.
	 *
	 * @param PaletteBuilder $paletteBuilder                         The palette builder in use.
	 */
	public function __construct($palettePropertyValueConditionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName);
		parent::__construct($paletteBuilder);
	}

	/**
	 * Set the class name.
	 *
	 * @param string $palettePropertyValueConditionClassName The class name.
	 *
	 * @return SetPalettePropertyValueConditionClassNameEvent
	 */
	public function setPalettePropertyValueConditionClassName($palettePropertyValueConditionClassName)
	{
		$this->palettePropertyValueConditionClassName = (string)$palettePropertyValueConditionClassName;

		return $this;
	}

	/**
	 * Retrieve the class name.
	 *
	 * @return string
	 */
	public function getPalettePropertyValueConditionClassName()
	{
		return $this->palettePropertyValueConditionClassName;
	}
}
