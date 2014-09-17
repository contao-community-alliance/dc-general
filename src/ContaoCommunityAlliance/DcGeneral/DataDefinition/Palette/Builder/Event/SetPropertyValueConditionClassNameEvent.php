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
 * This event gets emitted when a property value condition class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPropertyValueConditionClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-property-value-condition-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $propertyValueConditionClassName;

    /**
     * Create a new instance.
     *
     * @param string         $propertyValueConditionClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder                  The palette builder in use.
     */
    public function __construct($propertyValueConditionClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyValueConditionClassName($propertyValueConditionClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $propertyValueConditionClassName The class name.
     *
     * @return SetPropertyValueConditionClassNameEvent
     */
    public function setPropertyValueConditionClassName($propertyValueConditionClassName)
    {
        $this->propertyValueConditionClassName = (string)$propertyValueConditionClassName;
        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPropertyValueConditionClassName()
    {
        return $this->propertyValueConditionClassName;
    }
}
