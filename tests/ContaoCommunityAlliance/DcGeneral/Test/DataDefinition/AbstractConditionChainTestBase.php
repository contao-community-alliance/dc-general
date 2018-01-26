<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ReflectionProperty;

class AbstractConditionChainTestBase extends TestCase
{
    public function assertCloneMatch($condition)
    {
        $condition2 = clone $condition;

        $this->assertNotSame($condition, $condition2);

        $this->assertInstanceOf(get_class($condition), $condition2);
        $this->assertNotSame($condition, $condition2);
        $this->assertSame($condition->getConjunction(), $condition2->getConjunction());

        $reflection = new ReflectionProperty($condition, 'conditions');
        $reflection->setAccessible(true);

        $conditions  = $reflection->getValue($condition);
        $conditions2 = $reflection->getValue($condition2);

        $this->assertSame(count($conditions), count($conditions2));
        $this->assertSame(count($conditions), count(array_diff(array_keys($conditions), array_keys($conditions2))));

        reset($conditions);
        reset($conditions2);
        $subcondition  = current($conditions);
        $subcondition2 = current($conditions2);

        do {
            $this->assertSame(get_class($subcondition), get_class($subcondition2));

            next($conditions);
            next($conditions2);
            $subcondition  = current($conditions);
            $subcondition2 = current($conditions2);
        } while ($subcondition && $subcondition2);
    }
}
