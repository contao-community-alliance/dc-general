<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case for the relationship manager.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector
 */
class ModelCollectorTest extends TestCase
{
    /**
     * Test that construction bails without root condition.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::__construct()
     */
    public function testBailsForNoRootCondition()
    {
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_HIERARCHICAL);
        $relationships = $this->mockRelationshipDefinition();
        $definition    = $this->mockDefinitionContainer();
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $this->expectException(DcGeneralRuntimeException::class);

        new ModelCollector($environment);
    }

    /**
     * Data provider for the testGetModel().
     *
     * @return array
     */
    public function providerGetModel()
    {
        return [
            'fetch from explicit values' => ['test-id', 'provider-name'],
            'fetch from serialized id'   => [ModelId::fromValues('provider-name', 'test-id')->getSerialized(), null],
            'fetch by ModelId instance'  => [ModelId::fromValues('provider-name', 'test-id'), null],
        ];
    }

    /**
     * Test that the getModel() method works.
     *
     * @param string|ModelIdInterface $modelId      This is either the id of the model or a serialized id.
     * @param string|null             $providerName The name of the provider, if this is empty, the id will be
     *                                              deserialized and the provider name will get extracted from there.
     *
     * @return void
     *
     * @dataProvider providerGetModel
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel()
     */
    public function testGetModel($modelId, $providerName)
    {
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_FLAT);
        $relationships = $this->mockRelationshipDefinition();
        $propertiesDefinition = $this->mockPropertiesDefinition();
        $propertiesDefinition->method('getPropertyNames')->willReturn(['test-property']);
        $definition    = $this->mockDefinitionContainer();
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);
        $definition->method('getName')->willReturn($providerName);
        $definition->method('getPropertiesDefinition')->willReturn($propertiesDefinition);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config->expects(self::once())->method('setId')->with('test-id')->willReturn($config);

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn($config);
        $provider->method('fieldExists')->willReturn(true);
        $model = $this->getMockForAbstractClass(ModelInterface::class);
        $provider->expects(self::once())->method('fetch')->with($config)->willReturn($model);
        $environment->expects(self::once())->method('getDataProvider')->with('provider-name')->willReturn($provider);

        $collector = new ModelCollector($environment);

        // Test with parent definition
        if (false !== \strpos(\is_object($modelId) ? $modelId->getSerialized() : $modelId, '::')) {
            $parentPropertiesDefinition = $this->mockPropertiesDefinition();
            $parentPropertiesDefinition->method('getPropertyNames')->willReturn(['test-parent-property']);
            $parentDataDefinition = $this->mockDefinitionContainer();
            $parentDataDefinition->method('getName')->willReturn(ModelId::fromSerialized(
                \is_object($modelId) ? $modelId->getSerialized() : $modelId)->getDataProviderName());
            $parentDataDefinition->method('getPropertiesDefinition')->willReturn($parentPropertiesDefinition);

            $environment->method('getParentDataDefinition')->willReturn($parentDataDefinition);
        }

        self::assertSame($model, $collector->getModel($modelId, $providerName));
    }

    /**
     * Test that the getModel() throws an exception for invalid model ids.
     *
     * @return void
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel()
     */
    public function testGetModelThrowsExceptionForInvalidId()
    {
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_FLAT);
        $relationships = $this->mockRelationshipDefinition();
        $definition    = $this->mockDefinitionContainer();
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $collector = new ModelCollector($environment);

        $this->expectException('InvalidArgumentException');

        $collector->getModel(new \DateTime());
    }

    /**
     * Test the collectSiblingsOf() method.
     *
     * @return void
     */
    public function testCollectSiblingsOf()
    {
        $model           = $this->getMockForAbstractClass(ModelInterface::class);
        $rootCondition   = $this->getMockForAbstractClass(RootConditionInterface::class);
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_HIERARCHICAL);
        $basicDefinition->method('getRootDataProvider')->willReturn('root-provider');
        $relationships = $this->mockRelationshipDefinition();
        $definition    = $this->mockDefinitionContainer();
        $relationships->method('getRootCondition')->willReturn($rootCondition);
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);
        $rootCondition->method('getFilterArray')->willReturn([['local' => 'pid', 'remote' => 'id']]);
        $rootCondition->method('matches')->with($model)->willReturn(true);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config->expects(self::once())
            ->method('setFilter')
            ->with([['local' => 'pid', 'remote' => 'id']])
            ->willReturn($config);

        $configRegistry = $this->getMockForAbstractClass(BaseConfigRegistryInterface::class);
        $configRegistry->method('getBaseConfig')->with(null)->willReturn($config);
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);
        $environment->method('getBaseConfigRegistry')->willReturn($configRegistry);

        $collection = $this->getMockForAbstractClass(CollectionInterface::class);

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $model->method('getProviderName')->willReturn('root-provider');
        $provider->expects(self::once())->method('fetchAll')->with($config)->willReturn($collection);
        $environment->method('getDataProvider')->with('root-provider')->willReturn($provider);


        $collector = new ModelCollector($environment);

        self::assertSame($collection, $collector->collectSiblingsOf($model));
    }

    /**
     * Provides data for the testSearchParentOfInWithoutRecursion test.
     *
     * @return Generator
     */
    public function provideForTestSearchParentOfInWithoutRecursion(): Generator
    {
        $collection = new DefaultCollection();
        $collection->push($parentA = $this->createModel('parent', 1));
        $collection->push($parentB = $this->createModel('parent', 2));

        yield [
            $parentB,
            $this->createModel('child', 1, ['pid' => 2]),
            $collection,
        ];

        yield [
            $parentA,
            $this->createModel('child', 1, ['pid' => 1]),
            $collection
        ];

        yield [
            null,
            $this->createModel('child', 1, ['pid' => 3]),
            $collection
        ];
    }

    /**
     * Tests the searchParentOfIn method without recursion.
     *
     * @param ModelInterface|null             $expected   The expected parent.
     * @param ModelInterface                  $model      The given instance of the model.
     * @param CollectionInterface             $candidates The given candidates of the parent for the model.
     *
     * @return void
     *
     * @dataProvider provideForTestSearchParentOfInWithoutRecursion
     */
    public function testSearchParentOfInWithoutRecursion(
        ?ModelInterface $expected,
        ModelInterface $model,
        CollectionInterface $candidates
    ): void {
        $definition = $this->mockDefinitionContainer();

        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getDataProvider')->willReturn('child');
        $basicDefinition->method('getParentDataProvider')->willReturn('parent');
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_PARENTEDLIST);
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);

        $relationships = $this->mockRelationshipDefinition();
        $conditions    = [$this->createParentChildCondition('parent', 'child')];
        $relationships->method('getChildConditions')->willReturn($conditions);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config
            ->method('setFilter')
            ->willReturn($config);

        $parentProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $parentProvider->method('getEmptyConfig')->willReturn($config);
        $parentProvider->method('fetchAll')->with($config)->willReturn(new DefaultCollection());

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn($config);
        $provider->method('fetchAll')->with($config)->willReturn(new DefaultCollection());

        $environment->method('getDataProvider')->willReturnCallback(
            function (string $name) use ($provider, $parentProvider) {
                switch ($name) {
                    case 'child':
                        return $provider;
                    case 'parent':
                        return $parentProvider;

                    default:
                        throw new RuntimeException();
                }
            }
        );

        $collector = new ModelCollector($environment);

        self::assertSame($expected, $collector->searchParentOfIn($model, $candidates));
    }

    /**
     * Provides data for the testSearchParentOfWithRecursion test.
     *
     * @return Generator
     */
    public function provideForTestSearchParentOfInWithRecursion(): Generator
    {
        $parents = new DefaultCollection();
        $parents->push($parentA = $this->createModel('parent', 1, ['pid' => 10]));
        $parents->push($parentB = $this->createModel('parent', 2, ['pid' => 11]));

        $grandParents = new DefaultCollection();
        $grandParents->push($this->createModel('grandparent', 10));
        $grandParents->push($this->createModel('grandparent', 11));

        yield [
            $parentB,
            $this->createModel('child', 1, ['pid' => 2]),
            $parents,
            $grandParents,
        ];

        yield [
            $parentA,
            $this->createModel('child', 1, ['pid' => 1]),
            $parents,
            $grandParents,
        ];

        yield [
            null,
            $this->createModel('child', 1, ['pid' => 3]),
            $parents,
            $grandParents,
        ];
    }

    /**
     * Tests the searchParentOfIn method without recursion.
     *
     * @param ModelInterface|null $expected     The expected parent.
     * @param ModelInterface      $model        The given instance of the model.
     * @param CollectionInterface $parents      The given candidates of the parent for the model.
     * @param CollectionInterface $grandParents The given candidates of the parent for the model.
     *
     * @return void
     *
     * @dataProvider provideForTestSearchParentOfInWithRecursion
     */
    public function testSearchParentOfInWithRecursion(
        ?ModelInterface $expected,
        ModelInterface $model,
        CollectionInterface $parents,
        CollectionInterface $grandParents
    ): void {
        $definition      = $this->mockDefinitionContainer();
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getDataProvider')->willReturn('child');
        $basicDefinition->method('getParentDataProvider')->willReturn('parent');
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_PARENTEDLIST);
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);

        $relationships = $this->mockRelationshipDefinition();
        $relationships->method('getChildConditions')->willReturnCallback(
            function (string $providerName): array {
                switch ($providerName) {
                    case 'grandparent':
                        return [$this->createParentChildCondition('grandparent', 'parent')];

                    case  'parent':
                        return [$this->createParentChildCondition('parent', 'child')];

                    default:
                        throw new \RuntimeException();
                }
            }
        );

        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config
            ->method('setFilter')
            ->willReturn($config);

        $parentProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $parentProvider->method('getEmptyConfig')->willReturn($config);
        $parentProvider->method('fetchAll')->with($config)->willReturn($parents);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config
            ->method('setFilter')
            ->willReturn($config);

        $grandParentProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $grandParentProvider->method('getEmptyConfig')->willReturn($config);
        $grandParentProvider->method('fetchAll')->with($config)->willReturn(new DefaultCollection());

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn($config);
        $provider->method('fetchAll')->with($config)->willReturn(new DefaultCollection());

        $environment->method('getDataProvider')->willReturnCallback(
            function (string $name) use ($provider, $parentProvider, $grandParentProvider) {
                switch ($name) {
                    case 'child':
                        return $provider;
                    case 'parent':
                        return $parentProvider;
                    case 'grandparent':
                        return $grandParentProvider;

                    default:
                        throw new RuntimeException();
                }
            }
        );

        $collector = new ModelCollector($environment);
        self::assertSame($expected, $collector->searchParentOfIn($model, $grandParents));
    }

    /**
     * Provides data for the testSearchParentOfInHierarchical test.
     *
     * @return Generator
     */
    public function provideForTestSearchParentOfInHierarchicalByInverseFilter(): Generator
    {
        $parentNodeA = $this->createModel('node', 10);
        $parentNodeB = $this->createModel('node', 11);

        yield [
            $parentNodeA,
            $this->createModel('node', 1, ['pid' => 10, 'parentId' => 1]),
        ];

        yield [
            $parentNodeB,
            $this->createModel('node', 1, ['pid' => 11, 'parentId' => 2]),
        ];

        yield [
            null,
            $this->createModel('node', 1, ['pid' => 12, 'parentId' => 1]),
        ];
    }

    /**
     * Tests the searchParentOfIn method without recursion.
     *
     * When does ParentOfInHierarchical is ture. Per definition a ParentOfInHierarchical means that we are in them same
     * table all the time and all the data are mapped based on a parent <=> child, which is in the most cases that the
     * parent id is stored in the pid field of the child.
     * So if we check this, the source and the destination data provider have to be the same table. If not, we didn't
     * have a ParentOfInHierarchical definition.
     *
     * @param ModelInterface|null $expected The expected parent.
     * @param ModelInterface      $model    The given instance of the model.
     *
     * @return void
     *
     * @dataProvider provideForTestSearchParentOfInHierarchicalByInverseFilter
     */
    public function testSearchParentOfInHierarchicalByInverseFilter(
        ?ModelInterface $expected,
        ModelInterface $model
    ): void {
        $definition      = $this->mockDefinitionContainer();
        $basicDefinition = $this->mockBasicDefinition();
        $basicDefinition->method('getDataProvider')->willReturn('node');
        $basicDefinition->method('getRootDataProvider')->willReturn('node');
        $basicDefinition->method('getParentDataProvider')->willReturn('parent');
        $basicDefinition->method('getMode')->willReturn(BasicDefinitionInterface::MODE_HIERARCHICAL);
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);

        $relationships = $this->mockRelationshipDefinition();
        $relationships->method('getChildConditions')->willReturn(
            [
                $this->createParentChildCondition('parent', 'node'),
                $this->createParentChildCondition('node', 'node'),
            ]
        );

        $rootCondition = $this->getMockForAbstractClass(RootConditionInterface::class);
        $relationships->method('getRootCondition')->willReturn($rootCondition);

        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config->method('setFilter')->willReturn($config);

        $parentProvider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $parentProvider->method('getEmptyConfig')->willReturn($config);
        // There won't be a call to the parent, because we don't need it, we are in a hierarchical check.
        //$parentProvider
        //    ->expects($this->once())
        //    ->method('fetch')
        //    ->willReturn(null);

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn($config);
        $provider
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);

        $provider
            ->expects(null === $expected ? $this->once() : $this->never())
            ->method('fetchAll')
            ->willReturn(new DefaultCollection());

        $environment->method('getDataProvider')->willReturnCallback(
            function (string $name) use ($provider, $parentProvider) {
                switch ($name) {
                    case 'node':
                        return $provider;
                    case 'parent':
                        return $parentProvider;

                    default:
                        throw new \RuntimeException();
                }
            }
        );

        $collector = new ModelCollector($environment);
        self::assertSame($expected, $collector->searchParentOf($model));
    }

    /**
     * Mock a basic definition.
     *
     * @return BasicDefinitionInterface|MockObject
     */
    private function mockBasicDefinition()
    {
        return $this->getMockForAbstractClass(BasicDefinitionInterface::class);
    }

    /**
     * Mock a relationship definition.
     *
     * @return ModelRelationshipDefinitionInterface|MockObject
     */
    private function mockRelationshipDefinition()
    {
        return $this->getMockForAbstractClass(ModelRelationshipDefinitionInterface::class);
    }

    /**
     * Mock a definition container.
     *
     * @return ContainerInterface|MockObject
     */
    private function mockDefinitionContainer()
    {
        return $this->getMockForAbstractClass(ContainerInterface::class);
    }

    /**
     * Mock a definition container.
     *
     * @return PropertiesDefinitionInterface|MockObject
     */
    private function mockPropertiesDefinition()
    {
        return $this->getMockForAbstractClass(PropertiesDefinitionInterface::class);
    }

    private function createModel(string $providerName, int $id, array $properties = []): ModelInterface
    {
        $model = new DefaultModel();
        $model->setID($id);
        $model->setProviderName($providerName);
        $model->setPropertiesAsArray($properties);

        return $model;
    }

    private function createParentChildCondition(
        string $sourceName,
        string $destinationName
    ): ParentChildConditionInterface {
        $condition  = new ParentChildCondition();
        $condition->setSourceName($sourceName);
        $condition->setDestinationName($destinationName);
        $condition->setSetters(
            [
                [
                    'to_field'   => 'pid',
                    'from_field' => 'id',
                ],
            ]
        );
        $condition->setInverseFilterArray(
            [
                [
                    'local'     => 'pid',
                    'remote'    => 'id',
                    'operation' => '=',
                ],
            ]
        );

        $condition->setFilterArray(
            [
                [
                    'local'     => 'pid',
                    'remote'    => 'id',
                    'operation' => '=',
                ],
            ]
        );

        return $condition;
    }
}
