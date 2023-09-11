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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event gets emitted when a condition is created.
 *
 * @template TCondition of PaletteConditionInterface|PropertyConditionInterface
 */
class CreateConditionEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.create-condition';

    /**
     * The condition being created.
     *
     * @var TCondition
     */
    protected $condition;

    /**
     * Create a new instance.
     *
     * @param TCondition     $condition      The condition that has been created.
     * @param PaletteBuilder $paletteBuilder The palette builder that created the
     *                                       condition.
     */
    public function __construct($condition, PaletteBuilder $paletteBuilder)
    {
        $this->setCondition($condition);

        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param TCondition $condition The condition to use.
     *
     * @return CreateConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid condition has been passed.
     */
    public function setCondition($condition)
    {
        if (
            (!$condition instanceof PaletteConditionInterface)
            && (!$condition instanceof PropertyConditionInterface)
        ) {
            throw new DcGeneralInvalidArgumentException(
                'The condition is invalid. ' .
                ' Only use PaletteConditionInterface or PropertyConditionInterface.'
            );
        }

        $this->condition = $condition;

        return $this;
    }

    /**
     * Retrieve the condition.
     *
     * @return TCondition
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
