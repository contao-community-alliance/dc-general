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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the ParentChildCondition.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition
 */
class ParentChildConditionTest extends TestCase
{
    /**
     * Test that the matches method does not match for children from another provider.
     *
     * @return void
     */
    public function testMatchesForChildFromOtherProvider()
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test2-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray([
                    [
                        'local'     => 'id',
                        'operation' => '=',
                        'remote'    => 'pid'
                    ]
                ]
            )
            ->setSourceName('test-provider')
            ->setDestinationName('test-provider');

        self::assertFalse($condition->matches($parent, $child));
    }

    /**
     * Test that the matches method does not match for children from another provider.
     *
     * @return void
     */
    public function testMatchesForParentFromOtherProvider()
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray([
                    [
                        'local'     => 'id',
                        'operation' => '=',
                        'remote'    => 'pid'
                    ]
                ]
            )
            ->setSourceName('test-provider')
            ->setDestinationName('test-provider');

        self::assertFalse($condition->matches($parent, $child));
    }

    /**
     * Test that the matches method does not match when no provider name set.
     *
     * @return void
     */
    public function testMatchesForNoParentProvider()
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray([
                    [
                        'local'     => 'id',
                        'operation' => '=',
                        'remote'    => 'pid'
                    ]
                ]
            )
            ->setDestinationName('test-provider');

        self::assertFalse($condition->matches($parent, $child));
    }

    /**
     * Test that the matches method does not match when no provider name set.
     *
     * @return void
     */
    public function testMatchesForNoDestinationProvider()
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray([
                    [
                        'local'     => 'id',
                        'operation' => '=',
                        'remote'    => 'pid'
                    ]
                ]
            )
            ->setSourceName('test2-provider');

        self::assertFalse($condition->matches($parent, $child));
    }

    /**
     * Test the matches method().
     *
     * @return void
     */
    public function testMatches()
    {
        $parent = new DefaultModel();
        $parent->setID(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);

        $condition = new ParentChildCondition();
        $condition->setFilterArray([
                [
            'local'     => 'id',
            'operation' => '=',
            'remote'    => 'pid'
                ]
            ]
        );

        self::assertTrue($condition->matches($parent, $child));
    }

    /**
     * Test the matches method().
     *
     * @return void
     */
    public function testMatchesRemoteValue()
    {
        $parent = new DefaultModel();
        $parent->setID(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setID(2);

        $condition = new ParentChildCondition();
        $condition->setFilterArray([
                [
                    'local'        => 'id',
                    'operation'    => '=',
                    'remote_value' => '2'
                ]
            ]
        );

        self::assertTrue($condition->matches($parent, $child));
    }
}
