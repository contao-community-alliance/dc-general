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
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ParentChildCondition::class)]
final class ParentChildConditionTest extends TestCase
{
    public function testMatchesForChildFromOtherProvider(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test2-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray(
                [
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

    public function testMatchesForParentFromOtherProvider(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray(
                [
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

    public function testMatchesForNoParentProvider(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray(
                [
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

    public function testMatchesForNoDestinationProvider(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);
        $parent->setProviderName('test2-provider');

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setProviderName('test-provider');

        $condition = new ParentChildCondition();
        $condition
            ->setFilterArray(
                [
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

    public function testMatches(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);

        $condition = new ParentChildCondition();
        $condition->setFilterArray(
            [
                [
            'local'     => 'id',
            'operation' => '=',
            'remote'    => 'pid'
                ]
            ]
        );

        self::assertTrue($condition->matches($parent, $child));
    }

    public function testMatchesRemoteValue(): void
    {
        $parent = new DefaultModel();
        $parent->setID(1);

        $child = new DefaultModel();
        $child->setPropertyRaw('pid', 1);
        $child->setID(2);

        $condition = new ParentChildCondition();
        $condition->setFilterArray(
            [
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
