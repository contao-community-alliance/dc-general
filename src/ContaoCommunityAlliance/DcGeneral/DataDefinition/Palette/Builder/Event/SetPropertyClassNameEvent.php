<?php
/**
 * PHP version 5
 *
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
 * This event gets emitted when a property class name is set.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class SetPropertyClassNameEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.set-property-class-name';

    /**
     * The class name.
     *
     * @var string
     */
    protected $propertyClassName;

    /**
     * Create a new instance.
     *
     * @param string         $propertyClassName The class name.
     *
     * @param PaletteBuilder $paletteBuilder    The palette builder in use.
     */
    public function __construct($propertyClassName, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyClassName($propertyClassName);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the class name.
     *
     * @param string $propertyClassName The class name.
     *
     * @return SetPropertyClassNameEvent
     */
    public function setPropertyClassName($propertyClassName)
    {
        $this->propertyClassName = (string) $propertyClassName;

        return $this;
    }

    /**
     * Retrieve the class name.
     *
     * @return string
     */
    public function getPropertyClassName()
    {
        return $this->propertyClassName;
    }
}
