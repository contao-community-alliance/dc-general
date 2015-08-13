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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;

/**
 * This event gets emitted when a condition for the default palette is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreateDefaultPaletteConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-default-palette-condition';

    /**
     * The default palette condition.
     *
     * @var DefaultPaletteCondition
     */
    protected $paletteCondition;

    /**
     * Create a new instance.
     *
     * @param DefaultPaletteCondition $paletteCondition The condition that has been created.
     *
     * @param PaletteBuilder          $paletteBuilder   The palette builder creating the condition.
     */
    public function __construct(DefaultPaletteCondition $paletteCondition, PaletteBuilder $paletteBuilder)
    {
        $this->setDefaultPaletteCondition($paletteCondition);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param DefaultPaletteCondition $paletteCondition The condition.
     *
     * @return CreateDefaultPaletteConditionEvent
     */
    public function setDefaultPaletteCondition(DefaultPaletteCondition $paletteCondition)
    {
        $this->paletteCondition = $paletteCondition;

        return $this;
    }

    /**
     * Retrieve the condition.
     *
     * @return DefaultPaletteCondition
     */
    public function getDefaultPaletteCondition()
    {
        return $this->paletteCondition;
    }
}
