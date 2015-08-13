<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class FilterBuilderTest extends TestCase
{
    public function testEmpty()
    {
        $builder = new FilterBuilder();

        $this->assertEquals(array(), $builder->getAllAsArray());
    }

    public function testNoOp()
    {
        $filter = array(array('operation' => '=', 'property' => 'prop', 'value' => '1'));

        $builder = new FilterBuilder($filter, true);

        $this->assertEquals($filter, $builder->getAllAsArray());
    }

    public function testAddAnd()
    {
        $filter = array(array('operation' => '=', 'property' => 'prop', 'value' => '1'));
        $result = array_merge(
            $filter,
            array(array('operation' => '=', 'property' => 'prop2', 'value' => '2'))
        );

        $builder = new FilterBuilder($filter, true);

        $builder->getFilter()->andPropertyEquals('prop2', '2');

        $this->assertEquals($result, $builder->getAllAsArray());
    }
}
