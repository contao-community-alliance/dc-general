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

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

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

        $environment = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\EnvironmentInterface');
        $environment->method('getDataDefinition')->willReturn($definition);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException',
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
     *
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

        $environment = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\EnvironmentInterface');
        $environment->method('getDataDefinition')->willReturn($definition);

        $config = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface');
        $config->expects($this->once())->method('setId')->with('test-id')->willReturn($config);

        $provider = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface');
        $provider->method('getEmptyConfig')->willReturn($config);
        $model = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\Data\ModelInterface');
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

        $environment = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\EnvironmentInterface');
        $environment->method('getDataDefinition')->willReturn($definition);

        $collector = new ModelCollector($environment);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid model id passed: '
        );

        $collector->getModel(new \DateTime());
    }

    /**
     * Mock a basic definition.
     *
     * @return BasicDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockBasicDefinition()
    {
        $basicDefinition = $this->getMockForAbstractClass(
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface'
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
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface'
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
            'ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface'
        );

        return $definition;
    }
}
