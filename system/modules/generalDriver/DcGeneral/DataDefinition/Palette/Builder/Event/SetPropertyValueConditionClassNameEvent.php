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

class SetPropertyValueConditionClassNameEvent extends BuilderEvent
{
	const NAME = 'DcGeneral\DataDefinition\Palette\Builder\Event\SetPropertyValueConditionClassName';

	/**
	 * @var string
	 */
	protected $propertyValueConditionClassName;

	/**
	 * @param string               $propertyValueConditionClassName
	 * @param PaletteBuilder       $paletteBuilder
	 * @param EnvironmentInterface $environment
	 */
	function __construct($propertyValueConditionClassName, PaletteBuilder $paletteBuilder)
	{
		$this->setPropertyValueConditionClassName($propertyValueConditionClassName);
		$this->setPaletteBuilder($paletteBuilder);
	}

	/**
	 * @param string $propertyValueConditionClassName
	 */
	public function setPropertyValueConditionClassName($propertyValueConditionClassName)
	{
		$this->propertyValueConditionClassName = (string) $propertyValueConditionClassName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPropertyValueConditionClassName()
	{
		return $this->propertyValueConditionClassName;
	}

}
