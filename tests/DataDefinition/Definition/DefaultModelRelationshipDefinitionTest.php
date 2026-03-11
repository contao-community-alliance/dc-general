<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hopes to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * This tests the DefaultModelRelationshipDefinition.
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(DefaultModelRelationshipDefinition::class, 'setRootCondition')]
#[CoversMethod(DefaultModelRelationshipDefinition::class, 'getRootCondition')]
#[CoversMethod(DefaultModelRelationshipDefinition::class, 'getChildCondition')]
#[CoversMethod(DefaultModelRelationshipDefinition::class, 'getChildConditions')]
#[CoversMethod(DefaultModelRelationshipDefinition::class, '__clone')]
final class DefaultModelRelationshipDefinitionTest extends TestCase
{
    public function testSetGetRootCondition(): void
    {
        $definition = new DefaultModelRelationshipDefinition();
        $root       = $this->getMockBuilder(RootConditionInterface::class)->getMock();

        self::assertSame($definition, $definition->setRootCondition($root));
        self::assertSame($root, $definition->getRootCondition());
    }

    public function testAddGetChildCondition(): void
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('parent', 'child');

        self::assertSame($definition, $definition->addChildCondition($condition));
        self::assertSame($condition, $definition->getChildCondition('parent', 'child'));
    }

    public function testGetChildConditionWithoutMatch(): void
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('another-parent', 'child');

        $definition->addChildCondition($condition);
        self::assertNull($definition->getChildCondition('parent', 'child'));
    }

    public function testGetChildConditions(): void
    {
        $definition = new DefaultModelRelationshipDefinition();

        $definition->addChildCondition($condition1 = $this->mockChildCondition('parent', 'child1'));
        $definition->addChildCondition($condition2 = $this->mockChildCondition('parent', 'child2'));
        $definition->addChildCondition($this->mockChildCondition('parent2', 'child'));
        $definition->addChildCondition($condition3 = $this->mockChildCondition('parent', 'child3'));

        $conditions = $definition->getChildConditions('parent');

        self::assertEquals([$condition1, $condition2, $condition3], $conditions);
    }

    public function testGetChildConditionsFromEmpty(): void
    {
        $definition = new DefaultModelRelationshipDefinition();

        $conditions = $definition->getChildConditions('parent');

        self::assertEquals([], $conditions);
    }

    public function testGetChildConditionsReturnsAllWithoutSource(): void
    {
        $definition = new DefaultModelRelationshipDefinition();

        $definition->addChildCondition($condition1 = $this->mockChildCondition('parent', 'child1'));
        $definition->addChildCondition($condition2 = $this->mockChildCondition('parent', 'child2'));
        $definition->addChildCondition($condition3 = $this->mockChildCondition('parent2', 'child'));
        $definition->addChildCondition($condition4 = $this->mockChildCondition('parent', 'child3'));

        $conditions = $definition->getChildConditions();

        self::assertEquals([$condition1, $condition2, $condition3, $condition4], $conditions);
    }

    public function testClone(): void
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('parent', 'child');
        $root       = $this->getMockBuilder(RootConditionInterface::class)->getMock();

        $definition->addChildCondition($condition);
        $definition->setRootCondition($root);

        $definition2 = clone $definition;

        self::assertNotSame($root, $definition2->getRootCondition());
        self::assertInstanceOf(RootConditionInterface::class, $definition2->getRootCondition());

        self::assertNotSame($condition, $definition2->getChildCondition('parent', 'child'));
        self::assertInstanceOf(
            ParentChildConditionInterface::class,
            $definition2->getChildCondition('parent', 'child')
        );
    }

    /**
     * Mock a parent child's condition.
     *
     * @param string $source      The source name.
     * @param string $destination The destination name.
     */
    private function mockChildCondition(string $source, string $destination): ParentChildConditionInterface&MockObject
    {
        $condition = $this->getMockBuilder(ParentChildConditionInterface::class)->getMock();
        $condition->method('getSourceName')->willReturn($source);
        $condition->method('getDestinationName')->willReturn($destination);

        return $condition;
    }
}
