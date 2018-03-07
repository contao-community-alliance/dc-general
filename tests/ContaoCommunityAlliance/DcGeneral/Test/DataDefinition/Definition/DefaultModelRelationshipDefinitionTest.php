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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;

/**
 * This tests the DefaultModelRelationshipDefinition.
 */
class DefaultModelRelationshipDefinitionTest extends TestCase
{
    /**
     * Test the root condition setter and getter.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::setRootCondition()
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getRootCondition()
     */
    public function testSetGetRootCondition()
    {
        $definition = new DefaultModelRelationshipDefinition();
        $root       = $this->getMockForAbstractClass(
            RootConditionInterface::class
        );

        $this->assertSame($definition, $definition->setRootCondition($root));
        $this->assertSame($root, $definition->getRootCondition());
    }

    /**
     * Test the addition and retrieval of child conditions.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::addChildCondition()
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getChildCondition()
     */
    public function testAddGetChildCondition()
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('parent', 'child');

        $this->assertSame($definition, $definition->addChildCondition($condition));
        $this->assertSame($condition, $definition->getChildCondition('parent', 'child'));
    }

    /**
     * Test the retrieval of child conditions.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getChildCondition()
     */
    public function testGetChildConditionWithoutMatch()
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('another-parent', 'child');

        $definition->addChildCondition($condition);
        $this->assertNull($definition->getChildCondition('parent', 'child'));
    }

    /**
     * Test the retrieval of child conditions.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getChildConditions()
     */
    public function testGetChildConditions()
    {
        $definition = new DefaultModelRelationshipDefinition();

        $definition->addChildCondition($condition1 = $this->mockChildCondition('parent', 'child1'));
        $definition->addChildCondition($condition2 = $this->mockChildCondition('parent', 'child2'));
        $definition->addChildCondition($this->mockChildCondition('parent2', 'child'));
        $definition->addChildCondition($condition3 = $this->mockChildCondition('parent', 'child3'));

        $conditions = $definition->getChildConditions('parent');

        $this->assertEquals([$condition1, $condition2, $condition3], $conditions);
    }

    /**
     * Test the retrieval of child conditions from empty definition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getChildConditions()
     */
    public function testGetChildConditionsFromEmpty()
    {
        $definition = new DefaultModelRelationshipDefinition();

        $conditions = $definition->getChildConditions('parent');

        $this->assertEquals([], $conditions);
    }

    /**
     * Test the retrieval of all child conditions.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::getChildConditions()
     */
    public function testGetChildConditionsReturnsAllWithoutSource()
    {
        $definition = new DefaultModelRelationshipDefinition();

        $definition->addChildCondition($condition1 = $this->mockChildCondition('parent', 'child1'));
        $definition->addChildCondition($condition2 = $this->mockChildCondition('parent', 'child2'));
        $definition->addChildCondition($condition3 = $this->mockChildCondition('parent2', 'child'));
        $definition->addChildCondition($condition4 = $this->mockChildCondition('parent', 'child3'));

        $conditions = $definition->getChildConditions();

        $this->assertEquals([$condition1, $condition2, $condition3, $condition4], $conditions);
    }

    /**
     * Test that cloning also clones embedded objects.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition::__clone()
     */
    public function testClone()
    {
        $definition = new DefaultModelRelationshipDefinition();
        $condition  = $this->mockChildCondition('parent', 'child');
        $root       = $this->getMockForAbstractClass(
            RootConditionInterface::class
        );

        $definition->addChildCondition($condition);
        $definition->setRootCondition($root);

        $definition2 = clone $definition;

        $this->assertNotSame($root, $definition2->getRootCondition());
        $this->assertInstanceOf(
            RootConditionInterface::class,
            $definition2->getRootCondition()
        );

        $this->assertNotSame($condition, $definition2->getChildCondition('parent', 'child'));
        $this->assertInstanceOf(
            ParentChildConditionInterface::class,
            $definition2->getChildCondition('parent', 'child')
        );
    }

    /**
     * Mock a parent child condition.
     *
     * @param string $source      The source name.
     * @param string $destination The destination name.
     *
     * @return ParentChildConditionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockChildCondition($source, $destination)
    {
        $condition = $this->getMockForAbstractClass(
            ParentChildConditionInterface::class
        );
        $condition->method('getSourceName')->willReturn($source);
        $condition->method('getDestinationName')->willReturn($destination);

        return $condition;
    }
}
