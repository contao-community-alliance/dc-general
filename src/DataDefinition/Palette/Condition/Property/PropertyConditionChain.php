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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\AbstractConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A chain of property conditions.
 */
class PropertyConditionChain extends AbstractConditionChain implements PropertyConditionInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When an condition that does not implement PropertyConditionInterface
     *                                   is encountered.
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if ($this->conjunction == static::AND_CONJUNCTION) {
            foreach ($this->conditions as $condition) {
                if (!($condition instanceof PropertyConditionInterface)) {
                    throw new DcGeneralRuntimeException('Invalid condition in chain: ' . \get_class($condition));
                }

                if (!$condition->match($model, $input, $property, $legend)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($this->conditions as $condition) {
            if (!($condition instanceof PropertyConditionInterface)) {
                throw new DcGeneralRuntimeException('Invalid condition in chain: ' . \get_class($condition));
            }

            if ($condition->match($model, $input, $property, $legend)) {
                return true;
            }
        }

        return false;
    }
}
