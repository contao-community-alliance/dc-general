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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class ParentChildConditionTest extends TestCase
{
    public function testMatches()
    {
        $parent = new DefaultModel();
        $parent->setId(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);

        $condition = new ParentChildCondition();
        $condition->setFilterArray(array(array(
            'local'     => 'id',
            'operation' => '=',
            'remote'    => 'pid'
        )));

        $this->assertTrue($condition->matches($parent, $child));
    }

    public function testMatchesRemoteValue()
    {
        $parent = new DefaultModel();
        $parent->setId(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setId(2);

        $condition = new ParentChildCondition();
        $condition->setFilterArray(array(array(
            'local'        => 'id',
            'operation'    => '=',
            'remote_value' => '2'
        )));

        $this->assertTrue($condition->matches($parent, $child));
    }
}
