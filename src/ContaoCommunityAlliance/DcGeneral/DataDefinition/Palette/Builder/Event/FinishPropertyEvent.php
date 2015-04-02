<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This event gets emitted when a property is finished.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class FinishPropertyEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.finish-property';

    /**
     * The property.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * Create a new instance.
     *
     * @param PropertyInterface $property       The property.
     *
     * @param PaletteBuilder    $paletteBuilder The palette builder in use.
     */
    public function __construct(PropertyInterface $property, PaletteBuilder $paletteBuilder)
    {
        $this->setProperty($property);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property.
     *
     * @param PropertyInterface $property The property.
     *
     * @return FinishPropertyEvent
     */
    public function setProperty(PropertyInterface $property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Retrieve the property.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }
}
