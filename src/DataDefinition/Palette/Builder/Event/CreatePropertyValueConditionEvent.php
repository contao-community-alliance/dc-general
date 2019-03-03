<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
// @codingStandardsIgnoreStart
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition as PalettePropertyValueCondition;
// @codingStandardsIgnoreEnd
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event gets emitted when a property value condition gets created.
 */
class CreatePropertyValueConditionEvent extends BuilderEvent
{
    const NAME = 'dc-general.data-definition.palette.builder.create-property-value-condition';

    /**
     * The property value condition.
     *
     * @var PalettePropertyValueCondition|PropertyValueCondition
     */
    protected $condition;

    /**
     * Create a new instance.
     *
     * @param PalettePropertyValueCondition|PropertyValueCondition $condition      The condition.
     * @param PaletteBuilder                                       $paletteBuilder The palette builder in use.
     */
    public function __construct($condition, PaletteBuilder $paletteBuilder)
    {
        $this->setPropertyValueCondition($condition);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the property value condition.
     *
     * @param PalettePropertyValueCondition|PropertyValueCondition $condition The property value condition.
     *
     * @return CreatePropertyValueConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid condition has been passed.
     */
    public function setPropertyValueCondition($condition)
    {
        if (!($condition instanceof PalettePropertyValueCondition)
            && (!$condition instanceof PropertyValueCondition)
        ) {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->condition = $condition;
        return $this;
    }

    /**
     * Retrieve the property value condition.
     *
     * @return PalettePropertyValueCondition|PropertyValueCondition
     */
    public function getPropertyValueCondition()
    {
        return $this->condition;
    }
}
