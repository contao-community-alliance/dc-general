<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;

/**
 * This event gets emitted when a condition for the default palette is created.
 */
class CreateDefaultPaletteConditionEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.create-default-palette-condition';

    /**
     * The default palette condition.
     *
     * @var PaletteConditionInterface
     */
    protected $paletteCondition;

    /**
     * Create a new instance.
     *
     * @param PaletteConditionInterface $paletteCondition The condition that has been created.
     * @param PaletteBuilder            $paletteBuilder   The palette builder creating the condition.
     */
    public function __construct(PaletteConditionInterface $paletteCondition, PaletteBuilder $paletteBuilder)
    {
        $this->setDefaultPaletteCondition($paletteCondition);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param PaletteConditionInterface $paletteCondition The condition.
     *
     * @return CreateDefaultPaletteConditionEvent
     */
    public function setDefaultPaletteCondition(PaletteConditionInterface $paletteCondition)
    {
        $this->paletteCondition = $paletteCondition;

        return $this;
    }

    /**
     * Retrieve the condition.
     *
     * @return PaletteConditionInterface
     */
    public function getDefaultPaletteCondition()
    {
        return $this->paletteCondition;
    }
}
