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

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;

/**
 * Test case for the relationship manager.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RelationshipManagerTest extends TestCase
{
    /**
     * Test the isRoot() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot()
     */
    public function testIsRoot()
    {
        $model = $this->mockModel();
        $root  = $this->getMockForAbstractClass(
            RootConditionInterface::class
        );
        $root->expects(self::once())->method('matches')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects(self::once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->isRoot($model);
    }

    /**
     * Test the isRoot() method for non hierarchical mode.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot()
     */
    public function testIsRootInNonHierarchicalMode()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects(self::never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->isRoot($this->mockModel());
    }

    /**
     * Test the isRoot() without condition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot()
     */
    public function testIsRootWithoutCondition()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects(self::once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->expectException(DcGeneralRuntimeException::class);

        $manager->isRoot($this->mockModel());
    }

    /**
     * Test the setRoot() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot()
     */
    public function testSetRoot()
    {
        $model = $this->mockModel();
        $root  = $this->getMockForAbstractClass(
            RootConditionInterface::class
        );
        $root->expects(self::once())->method('applyTo')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects(self::once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setRoot($model);
    }

    /**
     * Test the setRoot() method for non hierarchical mode.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot()
     */
    public function testSetRootInNonHierarchicalMode()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects(self::never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->setRoot($this->mockModel());
    }

    /**
     * Test the setRoot() without condition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot()
     */
    public function testSetRootWithoutCondition()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects(self::once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

         $this->expectException(DcGeneralRuntimeException::class);

        $manager->setRoot($this->mockModel());
    }

    /**
     * Test the setParentForAll() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setAllRoot()
     */
    public function testSetAllRoot()
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);

        $manager = $this
            ->getMockBuilder(RelationshipManager::class)
            ->setMethods(['setRoot'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('setRoot')->withConsecutive([$model1], [$model2]);

        /** @var RelationshipManager $manager */
        $manager->setAllRoot($collection);
    }

    /**
     * Test the setParent() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent()
     */
    public function testSetParent()
    {
        $model     = $this->mockModel();
        $parent    = $this->mockModel();
        $condition = $this->getMockForAbstractClass(ParentChildConditionInterface::class);
        $condition->expects(self::once())->method('applyTo')->with($model);

        $model->method('getProviderName')->willReturn('child');
        $parent->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects(self::once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn($condition);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setParent($model, $parent);
    }

    /**
     * Test the setParent() without condition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent()
     */
    public function testSetParentWithoutCondition()
    {
        $model  = $this->mockModel();
        $parent = $this->mockModel();
        $model->method('getProviderName')->willReturn('child');
        $parent->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects(self::once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

         $this->expectException(DcGeneralRuntimeException::class);

        $manager->setParent($model, $parent);
    }

    /**
     * Test the setParentForAll() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParentForAll()
     */
    public function testSetParentForAll()
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);

        $parent  = $this->mockModel();
        $manager = $this
            ->getMockBuilder(RelationshipManager::class)
            ->setMethods(['setParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('setParent')->withConsecutive(
            [$model1, $parent],
            [$model2, $parent]
        );

        /** @var RelationshipManager $manager */
        $manager->setParentForAll($collection, $parent);
    }

    /**
     * Test the setSameParent() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent()
     */
    public function testSetSameParent()
    {
        $model     = $this->mockModel();
        $source    = $this->mockModel();
        $condition = $this->getMockForAbstractClass(
            ParentChildConditionInterface::class
        );
        $condition->expects(self::once())->method('copyFrom')->with($model, $source);

        $model->method('getProviderName')->willReturn('child');
        $source->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects(self::once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn($condition);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setSameParent($model, $source, 'parent');
    }

    /**
     * Test the setSameParent() without condition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent()
     */
    public function testSetSameParentWithoutCondition()
    {
        $model  = $this->mockModel();
        $source = $this->mockModel();
        $model->method('getProviderName')->willReturn('child');
        $source->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects(self::once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->expectException(DcGeneralRuntimeException::class);

        $manager->setSameParent($model, $source, 'parent');
    }

    /**
     * Test the setSameParentForAll() method.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParentForAll()
     */
    public function testSetSameParentForAll()
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $source     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);
        $model1->method('getProviderName')->willReturn('child');
        $model2->method('getProviderName')->willReturn('child');
        $source->method('getProviderName')->willReturn('child');

        $manager = $this
            ->getMockBuilder(RelationshipManager::class)
            ->setMethods(['setSameParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::exactly(2))->method('setSameParent')->withConsecutive(
            [$model1, $source, 'parent'],
            [$model2, $source, 'parent']
        );

        /** @var RelationshipManager $manager */
        $manager->setSameParentForAll($collection, $source, 'parent');
    }

    /**
     * Mock a model relationship.
     *
     * @return ModelRelationshipDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockRelationship()
    {
        return $this->getMockForAbstractClass(ModelRelationshipDefinitionInterface::class);
    }

    /**
     * Mock a model.
     *
     * @return ModelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockModel()
    {
        return $this->getMockForAbstractClass(ModelInterface::class);
    }
}
