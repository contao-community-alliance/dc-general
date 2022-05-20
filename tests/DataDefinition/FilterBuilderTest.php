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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\FilterBuilder
 */
class FilterBuilderTest extends TestCase
{
    public function testEmpty()
    {
        $builder = new FilterBuilder();

        self::assertEquals([], $builder->getAllAsArray());
    }

    public function testNoOp()
    {
        $filter = [['operation' => '=', 'property' => 'prop', 'value' => '1']];

        $builder = new FilterBuilder($filter, true);

        self::assertEquals($filter, $builder->getAllAsArray());
    }

    public function testAddAnd()
    {
        $filter = [['operation' => '=', 'property' => 'prop', 'value' => '1']];
        $result = \array_merge($filter, [['operation' => '=', 'property' => 'prop2', 'value' => '2']]);

        $builder = new FilterBuilder($filter, true);

        $builder->getFilter()->andPropertyEquals('prop2', '2');

        self::assertEquals($result, $builder->getAllAsArray());
    }
}
