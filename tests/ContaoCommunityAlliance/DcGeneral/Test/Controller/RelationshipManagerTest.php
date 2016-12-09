<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

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
     */
    public function testIsRoot()
    {
        $model = $this->mockModel();
        $root  = $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface'
        );
        $root->expects($this->once())->method('matches')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->isRoot($model);
    }

    /**
     * Test the isRoot() method for non hierarchical mode.
     *
     * @return void
     */
    public function testIsRootInNonHierarchicalMode()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->isRoot($this->mockModel());
    }

    /**
     * Test the isRoot() without condition.
     *
     * @return void
     */
    public function testIsRootWithoutCondition()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException',
            'No root condition defined'
        );

        $manager->isRoot($this->mockModel());
    }

    /**
     * Test the setRoot() method.
     *
     * @return void
     */
    public function testSetRoot()
    {
        $model = $this->mockModel();
        $root  = $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface'
        );
        $root->expects($this->once())->method('applyTo')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setRoot($model);
    }

    /**
     * Test the setRoot() method for non hierarchical mode.
     *
     * @return void
     */
    public function testSetRootInNonHierarchicalMode()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->setRoot($this->mockModel());
    }

    /**
     * Test the setRoot() without condition.
     *
     * @return void
     */
    public function testSetRootWithoutCondition()
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException',
            'No root condition defined'
        );

        $manager->setRoot($this->mockModel());
    }

    /**
     * Test the setParentForAll() method.
     *
     * @return void
     */
    public function testSetAllRoot()
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);

        $manager = $this
            ->getMockBuilder('ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager')
            ->setMethods(['setRoot'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->exactly(2))->method('setRoot')->withConsecutive(
            [$model1],
            [$model2]
        );

        /** @var RelationshipManager $manager */
        $manager->setAllRoot($collection);
    }

    /**
     * Test the setParent() method.
     *
     * @return void
     */
    public function testSetParent()
    {
        $model     = $this->mockModel();
        $parent    = $this->mockModel();
        $condition = $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface'
        );
        $condition->expects($this->once())->method('applyTo')->with($model);

        $model->expects($this->any())->method('getProviderName')->willReturn('child');
        $parent->expects($this->any())->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
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
     */
    public function testSetParentWithoutCondition()
    {
        $model  = $this->mockModel();
        $parent = $this->mockModel();
        $model->expects($this->any())->method('getProviderName')->willReturn('child');
        $parent->expects($this->any())->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException',
            'No condition defined from parent to child'
        );

        $manager->setParent($model, $parent);
    }

    /**
     * Test the setParentForAll() method.
     *
     * @return void
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
            ->getMockBuilder('ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager')
            ->setMethods(['setParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->exactly(2))->method('setParent')->withConsecutive(
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
     */
    public function testSetSameParent()
    {
        $model     = $this->mockModel();
        $source    = $this->mockModel();
        $condition = $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface'
        );
        $condition->expects($this->once())->method('copyFrom')->with($model, $source);

        $model->expects($this->any())->method('getProviderName')->willReturn('child');
        $source->expects($this->any())->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
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
     */
    public function testSetSameParentWithoutCondition()
    {
        $model  = $this->mockModel();
        $source = $this->mockModel();
        $model->expects($this->any())->method('getProviderName')->willReturn('child');
        $source->expects($this->any())->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException',
            'No condition defined from parent to child'
        );

        $manager->setSameParent($model, $source, 'parent');
    }

    /**
     * Test the setSameParentForAll() method.
     *
     * @return void
     */
    public function testSetSameParentForAll()
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $source     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);
        $model1->expects($this->any())->method('getProviderName')->willReturn('child');
        $model2->expects($this->any())->method('getProviderName')->willReturn('child');
        $source->expects($this->any())->method('getProviderName')->willReturn('child');

        $manager = $this
            ->getMockBuilder('ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager')
            ->setMethods(['setSameParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->exactly(2))->method('setSameParent')->withConsecutive(
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
        return $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface'
        );
    }

    /**
     * Mock a model.
     *
     * @return ModelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockModel()
    {
        return $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\Data\ModelInterface');
    }
}
