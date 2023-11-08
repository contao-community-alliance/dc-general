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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This event is emitted when an condition is added by the palette builder.
 *
 * @template TCondition of PaletteConditionInterface|PropertyConditionInterface
 * @template TTarget of PaletteInterface|PropertyInterface
 */
class AddConditionEvent extends BuilderEvent
{
    public const NAME = 'dc-general.data-definition.palette.builder.add-condition';

    /**
     * The condition that is being added.
     *
     * @var TCondition
     */
    protected $condition;

    /**
     * The target to which the condition is being added.
     *
     * @var TTarget
     */
    protected $target;

    /**
     * Create a new instance.
     *
     * @param TCondition     $condition      The condition being added.
     * @param TTarget        $target         The target property or palette.
     * @param PaletteBuilder $paletteBuilder The palette builder in use.
     */
    public function __construct($condition, $target, PaletteBuilder $paletteBuilder)
    {
        $this->setCondition($condition);
        $this->setTarget($target);
        parent::__construct($paletteBuilder);
    }

    /**
     * Set the condition.
     *
     * @param TCondition $condition The condition.
     *
     * @return AddConditionEvent
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

    /**
     * Set the target.
     *
     * @param TTarget $target The target property or palette.
     *
     * @return AddConditionEvent
     *
     * @throws DcGeneralInvalidArgumentException When an invalid target has been passed.
     */
    public function setTarget($target)
    {
        if ((!$target instanceof PaletteInterface) && (!$target instanceof PropertyInterface)) {
            throw new DcGeneralInvalidArgumentException();
        }

        $this->target = $target;
        return $this;
    }

    /**
     * Retrieve the target.
     *
     * @return TTarget
     */
    public function getTarget()
    {
        return $this->target;
    }
}
