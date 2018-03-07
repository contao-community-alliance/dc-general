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

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;

/**
 * Test case for the relationship manager.
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

        $this->setExpectedException(
            DcGeneralRuntimeException::class,
            'No root condition specified for hierarchical mode.'
        );

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
        $definition    = $this->mockDefinitionContainer();
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);

        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config->expects($this->once())->method('setId')->with('test-id')->willReturn($config);

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn($config);
        $model = $this->getMockForAbstractClass(ModelInterface::class);
        $provider->expects($this->once())->method('fetch')->with($config)->willReturn($model);
        $environment->expects($this->once())->method('getDataProvider')->with('provider-name')->willReturn($provider);

        $collector = new ModelCollector($environment);

        $this->assertSame($model, $collector->getModel($modelId, $providerName));
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

        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid model id passed: '
        );

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
        $relationships->expects($this->any())->method('getRootCondition')->willReturn($rootCondition);
        $definition->method('getBasicDefinition')->willReturn($basicDefinition);
        $definition->method('getModelRelationshipDefinition')->willReturn($relationships);
        $rootCondition->method('getFilterArray')->willReturn([['local' => 'pid', 'remote' => 'id']]);
        $rootCondition->method('matches')->with($model)->willReturn(true);

        $config = $this->getMockForAbstractClass(ConfigInterface::class);
        $config->expects($this->once())->method('setFilter')->with([['local' => 'pid', 'remote' => 'id']])->willReturn($config);

        $configRegistry = $this->getMockForAbstractClass(BaseConfigRegistryInterface::class);
        $configRegistry->method('getBaseConfig')->with(null)->willReturn($config);
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);
        $environment->method('getBaseConfigRegistry')->willReturn($configRegistry);

        $collection = $this->getMockForAbstractClass(CollectionInterface::class);

        $provider = $this->getMockForAbstractClass(DataProviderInterface::class);
        $model->expects($this->any())->method('getProviderName')->willReturn('root-provider');
        $provider->expects($this->once())->method('fetchAll')->with($config)->willReturn($collection);
        $environment->method('getDataProvider')->with('root-provider')->willReturn($provider);


        $collector = new ModelCollector($environment);

        $this->assertSame($collection, $collector->collectSiblingsOf($model));
    }

    /**
     * Mock a basic definition.
     *
     * @return BasicDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockBasicDefinition()
    {
        $basicDefinition = $this->getMockForAbstractClass(
            BasicDefinitionInterface::class
        );

        return $basicDefinition;
    }

    /**
     * Mock a relationship definition.
     *
     * @return \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockRelationshipDefinition()
    {
        $relationships = $this->getMockForAbstractClass(
            ModelRelationshipDefinitionInterface::class
        );

        return $relationships;
    }

    /**
     * Mock a definition container.
     *
     * @return \ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDefinitionContainer()
    {
        $definition = $this->getMockForAbstractClass(
            ContainerInterface::class
        );

        return $definition;
    }
}
