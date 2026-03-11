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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case for the relationship manager.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(RelationshipManager::class, 'isRoot')]
#[CoversMethod(RelationshipManager::class, 'setParentForAll')]
#[CoversMethod(RelationshipManager::class, 'setRoot')]
#[CoversMethod(RelationshipManager::class, 'setSameParent')]
#[CoversMethod(RelationshipManager::class, 'setSameParentForAll')]
final class RelationshipManagerTest extends TestCase
{
    public function testIsRoot(): void
    {
        $model = $this->mockModel();
        $root  = $this->getMockBuilder(RootConditionInterface::class)->getMock();
        $root->expects($this->once())->method('matches')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->isRoot($model);
    }

    public function testIsRootInNonHierarchicalMode(): void
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->isRoot($this->mockModel());
    }

    public function testIsRootWithoutCondition(): void
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->expectException(DcGeneralRuntimeException::class);

        $manager->isRoot($this->mockModel());
    }

    public function testSetRoot(): void
    {
        $model = $this->mockModel();
        $root  = $this->getMockBuilder(RootConditionInterface::class)->getMock();
        $root->expects($this->once())->method('applyTo')->with($model);

        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition')->willReturn($root);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setRoot($model);
    }

    public function testSetRootInNonHierarchicalMode(): void
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->never())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_FLAT);

        $manager->setRoot($this->mockModel());
    }

    public function testSetRootWithoutCondition(): void
    {
        $relationships = $this->mockRelationship();
        $relationships->expects($this->once())->method('getRootCondition');

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

         $this->expectException(DcGeneralRuntimeException::class);

        $manager->setRoot($this->mockModel());
    }

    public function testSetAllRoot(): void
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);

        $manager = $this
            ->getMockBuilder(RelationshipManager::class)
            ->onlyMethods(['setRoot'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager
            ->expects($this->exactly(2))
            ->method('setRoot')
            ->willReturnCallback(
                static function (ModelInterface $model) use ($model1, $model2): void {
                    static $counter = 0;
                    switch ($counter++) {
                        case 0:
                            self::assertSame($model1, $model);
                            return;
                        case 1:
                            self::assertSame($model2, $model);
                            return;
                    }
                    self::fail('Unexpected call');
                }
            );

        /** @var RelationshipManager $manager */
        $manager->setAllRoot($collection);
    }

    public function testSetParent(): void
    {
        $model     = $this->mockModel();
        $parent    = $this->mockModel();
        $condition = $this->getMockBuilder(ParentChildConditionInterface::class)->getMock();
        $condition->expects($this->once())->method('applyTo')->with($model);

        $model->method('getProviderName')->willReturn('child');
        $parent->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn($condition);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setParent($model, $parent);
    }

    public function testSetParentWithoutCondition(): void
    {
        $model  = $this->mockModel();
        $parent = $this->mockModel();
        $model->method('getProviderName')->willReturn('child');
        $parent->method('getProviderName')->willReturn('parent');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

         $this->expectException(DcGeneralRuntimeException::class);

        $manager->setParent($model, $parent);
    }

    public function testSetParentForAll(): void
    {
        $model1     = $this->mockModel();
        $model2     = $this->mockModel();
        $collection = new DefaultCollection();
        $collection->push($model1);
        $collection->push($model2);

        $parent  = $this->mockModel();
        $manager = $this
            ->getMockBuilder(RelationshipManager::class)
            ->onlyMethods(['setParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager
            ->expects($this->exactly(2))
            ->method('setParent')
            ->willReturnCallback(
                static function (
                    ModelInterface $model,
                    ModelInterface $parentModel
                ) use (
                    $model1,
                    $model2,
                    $parent
                ): void {
                    static $counter = 0;
                    switch ($counter++) {
                        case 0:
                            self::assertSame($model1, $model);
                            self::assertSame($parent, $parentModel);
                            return;
                        case 1:
                            self::assertSame($model2, $model);
                            self::assertSame($parent, $parentModel);
                            return;
                    }
                    self::fail('Unexpected call');
                }
            );

        /** @var RelationshipManager $manager */
        $manager->setParentForAll($collection, $parent);
    }

    public function testSetSameParent(): void
    {
        $model     = $this->mockModel();
        $source    = $this->mockModel();
        $condition = $this->getMockBuilder(ParentChildConditionInterface::class)->getMock();
        $condition->expects($this->once())->method('copyFrom')->with($model, $source);

        $model->method('getProviderName')->willReturn('child');
        $source->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn($condition);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $manager->setSameParent($model, $source, 'parent');
    }

    public function testSetSameParentWithoutCondition(): void
    {
        $model  = $this->mockModel();
        $source = $this->mockModel();
        $model->method('getProviderName')->willReturn('child');
        $source->method('getProviderName')->willReturn('child');

        $relationships = $this->mockRelationship();
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('parent', 'child')
            ->willReturn(null);

        $manager = new RelationshipManager($relationships, BasicDefinitionInterface::MODE_HIERARCHICAL);

        $this->expectException(DcGeneralRuntimeException::class);

        $manager->setSameParent($model, $source, 'parent');
    }

    public function testSetSameParentForAll(): void
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
            ->onlyMethods(['setSameParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $manager
            ->expects($this->exactly(2))
            ->method('setSameParent')
            ->willReturnCallback(
                static function (
                    ModelInterface $model,
                    ModelInterface $sourceModel,
                    string $providerName
                ) use (
                    $model1,
                    $model2,
                    $source
                ): void {
                    static $counter = 0;
                    switch ($counter++) {
                        case 0:
                            self::assertSame($model1, $model);
                            self::assertSame($source, $sourceModel);
                            self::assertSame('parent', $providerName);
                            return;
                        case 1:
                            self::assertSame($model2, $model);
                            self::assertSame($source, $sourceModel);
                            self::assertSame('parent', $providerName);
                            return;
                    }
                    self::fail('Unexpected call');
                }
            );

        /** @var RelationshipManager $manager */
        $manager->setSameParentForAll($collection, $source, 'parent');
    }

    /**
     * Mock a model relationship.
     */
    private function mockRelationship(): ModelRelationshipDefinitionInterface&MockObject
    {
        return $this->getMockBuilder(ModelRelationshipDefinitionInterface::class)->getMock();
    }

    /**
     * Mock a model.
     */
    private function mockModel(): ModelInterface&MockObject
    {
        return $this->getMockBuilder(ModelInterface::class)->getMock();
    }
}
