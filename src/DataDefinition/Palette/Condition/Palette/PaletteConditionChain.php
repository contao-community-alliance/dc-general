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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\AbstractConditionChain;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * A chain of palette conditions.
 */
class PaletteConditionChain extends AbstractConditionChain implements PaletteConditionInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When a condition that does not implement PaletteConditionInterface
     *                                   is encountered.
     */
    public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        $totalCount = false;

        foreach ($this->conditions as $condition) {
            if (!($condition instanceof PaletteConditionInterface)) {
                throw new DcGeneralRuntimeException('Invalid condition in chain: ' . \get_class($condition));
            }

            $conditionCount = $condition->getMatchCount($model, $input);

            if (false !== $conditionCount) {
                if (false === $totalCount) {
                    $totalCount = 0;
                }
                $totalCount += $conditionCount;
            } elseif (static::AND_CONJUNCTION === $this->conjunction) {
                return false;
            }
        }

        return $totalCount;
    }
}
