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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test the base configuration registry.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry
 */
class BaseConfigRegistryTest extends TestCase
{
    public function testSetterAndGetter()
    {
        $environment = $this->getMockBuilder(EnvironmentInterface::class)->getMock();

        $configRegistry = new BaseConfigRegistry();

        self::assertNull($configRegistry->getEnvironment());

        self::assertInstanceOf(BaseConfigRegistryInterface::class, $configRegistry->setEnvironment($environment));
        self::assertInstanceOf(EnvironmentInterface::class, $configRegistry->getEnvironment());
        self::assertSame($environment, $configRegistry->getEnvironment());
    }

    public function testGetBaseConfig()
    {
        // Common test settings.
        $basicDefinition      =
            $this->getMockBuilder(DefaultBasicDefinition::class)->enableProxyingToOriginalMethods()->getMock();
        $dataDefinition       =
            $this->getMockBuilder(DefaultContainer::class)->disableOriginalConstructor()->getMock();
        $environment          =
            $this->getMockBuilder(DefaultEnvironment::class)->setMethods(['getDataDefinition'])->getMock();
        $viewDefinition       = $this->createMock(Contao2BackendViewDefinitionInterface::class);
        $listingConfig        = $this->getMockBuilder(ListingConfigInterface::class)->getMock();
        $modelRelationShip    = $this->createMock(ModelRelationshipDefinitionInterface::class);
        $parentChildCondition = $this->createMock(ParentChildConditionInterface::class);

        $viewDefinition->method('getListingConfig')->willReturn($listingConfig);

        $definition = [
            Contao2BackendViewDefinitionInterface::NAME => $viewDefinition
        ];
        $dataDefinition->method('hasDefinition')->will(
            self::returnCallback(
                function ($definitionName) use ($definition) {
                    return array_key_exists($definitionName, $definition);
                }
            )
        );
        $dataDefinition->method('getDefinition')->will(
            self::returnCallback(
                function ($definitionName) use ($definition) {
                    return $definition[$definitionName];
                }
            )
        );
        $dataDefinition->method('getModelRelationshipDefinition')->willReturn($modelRelationShip);

        $parentChildFilter = ['child' => 'bar'];
        $parentChildCondition->method('getFilter')->willReturn($parentChildFilter);

        $modelRelationShip->method('getChildCondition')->will(
            self::returnCallback(
                function ($parentProviderName) use ($parentChildCondition) {
                    if ('parentIdWithCondition' === $parentProviderName) {
                        return $parentChildCondition;
                    }

                    return null;
                }
            )
        );

        $environment->method('getDataDefinition')->willReturn($dataDefinition);

        $configRegistry = new BaseConfigRegistry();
        $configRegistry->setEnvironment($environment);

        // Single data provider test settings.
        $dataDefinition->method('getBasicDefinition')->willReturn($basicDefinition);
        $singleDataProvider       = $this->createMock(DefaultDataProvider::class);
        $singleDataProviderConfig = DefaultConfig::init();
        $singleDataProvider->method('getEmptyConfig')->willReturn($singleDataProviderConfig);
        $singleAdditionalFilter  = ['single' => 'foo'];
        $singleAdditionalFilter2 = ['single' => 'bar'];

        // Single data provider tests.
        $environment->addDataProvider('single', $singleDataProvider);
        $basicDefinition->setDataProvider('single');
        $basicDefinition->setAdditionalFilter('single', $singleAdditionalFilter);
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig(null));
        self::assertIsArray($singleDataProviderConfig->getFilter());
        self::assertSame($singleAdditionalFilter, $singleDataProviderConfig->getFilter());
        // Test get single data provider from cache.
        $basicDefinition->setAdditionalFilter('single', $singleAdditionalFilter2);
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig(null));
        self::assertIsArray($singleDataProviderConfig->getFilter());
        self::assertNotSame($singleAdditionalFilter2, $singleDataProviderConfig->getFilter());

        // ParentId data provider test settings.
        $parentIdDataProvider       = $this->createMock(DefaultDataProvider::class);
        $parentIdDataProviderConfig = DefaultConfig::init();
        $parentIdDataProvider->method('getEmptyConfig')->willReturn($parentIdDataProviderConfig);

        // ParentId data provider test exception unexpected parent provider.
        $unexpectedModelId = ModelId::fromValues('parentId', 'unexpected-parent-provider');
        $environment->addDataProvider('parentId', $parentIdDataProvider);
        $basicDefinition->setParentDataProvider('unexpectedDataProvider');
        try {
            $configRegistry->getBaseConfig($unexpectedModelId);
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame(
                'Unexpected parent provider parentId (expected unexpectedDataProvider)',
                $exception->getMessage()
            );
        }

        // ParentId data provider test exception parent item not found.
        $itemNotFoundModelId = ModelId::fromValues('parentId', 'item-not-found');
        $basicDefinition->setParentDataProvider('parentId');
        try {
            $configRegistry->getBaseConfig($itemNotFoundModelId);
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame(
                'Parent item parentId::Iml0ZW0tbm90LWZvdW5kIg== not found in parentId',
                $exception->getMessage()
            );
        }

        // ParentId data provider tests.
        $modelId                  = ModelId::fromValues('parentId', 'id-parent');
        $parentIdModel            = $this->createMock(DefaultModel::class);
        $parentIdAdditionalFilter = ['parentId' => 'foo'];
        $parentIdDataProvider->method('fetch')->willReturn($parentIdModel);
        $basicDefinition->setAdditionalFilter('single', $singleAdditionalFilter);
        $basicDefinition->setAdditionalFilter('parentId', $parentIdAdditionalFilter);
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig($modelId));
        self::assertIsArray($singleDataProviderConfig->getFilter());
        self::assertSame($singleAdditionalFilter, $singleDataProviderConfig->getFilter());

        // ParentId data provider tests with child condition.
        $modelIdWithChildCondition = ModelId::fromValues('parentIdWithCondition', 'id-parent-with-child-condition');
        $environment->addDataProvider('parentIdWithCondition', $parentIdDataProvider);
        $basicDefinition->setParentDataProvider('parentIdWithCondition');
        $exceptedFilterWithChildCondition = [
            [
                'operation' => 'AND',
                'children'  => array_merge($singleAdditionalFilter, $parentChildFilter)
            ]
        ];
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig($modelIdWithChildCondition));
        self::assertIsArray($singleDataProviderConfig->getFilter());
        self::assertSame($exceptedFilterWithChildCondition, $singleDataProviderConfig->getFilter());
    }

    public function testGetBaseConfigParentListMode()
    {
        $basicDefinition    =
            $this->getMockBuilder(DefaultBasicDefinition::class)->enableProxyingToOriginalMethods()->getMock();
        $dataDefinition     =
            $this->getMockBuilder(DefaultContainer::class)->disableOriginalConstructor()->getMock();
        $environment        =
            $this
                ->getMockBuilder(DefaultEnvironment::class)
                ->setMethods(
                    [
                        'getDataDefinition',
                        'getInputProvider'
                    ]
                )
                ->getMock();
        $viewDefinition     = $this->createMock(Contao2BackendViewDefinitionInterface::class);
        $listingConfig      = $this->getMockBuilder(ListingConfigInterface::class)->getMock();
        $dataProvider       = $this->createMock(DefaultDataProvider::class);
        $modelRelationShip  = $this->createMock(ModelRelationshipDefinitionInterface::class);
        $parentDataProvider = $this->createMock(DefaultDataProvider::class);

        $dataProviderConfig = DefaultConfig::init();
        $dataProvider->method('getEmptyConfig')->willReturn($dataProviderConfig);

        $viewDefinition->method('getListingConfig')->willReturn($listingConfig);

        $modelRelationShip->method('getChildCondition')->willReturn(null);

        $dataDefinition->method('getModelRelationshipDefinition')->willReturn($modelRelationShip);
        $definition = [
            Contao2BackendViewDefinitionInterface::NAME => $viewDefinition
        ];
        $dataDefinition->method('hasDefinition')->will(
            self::returnCallback(
                function ($definitionName) use ($definition) {
                    return array_key_exists($definitionName, $definition);
                }
            )
        );
        $dataDefinition->method('getDefinition')->will(
            self::returnCallback(
                function ($definitionName) use ($definition) {
                    return $definition[$definitionName];
                }
            )
        );

        $inputProvider = $this->createMock(InputProviderInterface::class);

        $environment->method('getDataDefinition')->willReturn($dataDefinition);
        $environment->method('getInputProvider')->willReturn($inputProvider);

        $dataDefinition->method('getBasicDefinition')->willReturn($basicDefinition);

        $parentModel = $this->createMock(DefaultModel::class);
        $parentDataProvider->method('fetch')->willReturn($parentModel);
        $parentDataProviderConfig = DefaultConfig::init();
        $parentDataProvider->method('getEmptyConfig')->willReturn($parentDataProviderConfig);

        $configRegistry = new BaseConfigRegistry();
        $configRegistry->setEnvironment($environment);

        $parentListModelId = ModelId::fromValues('parent-list', 'id-parent');
        $inputProvider->method('getParameter')->willReturn($parentListModelId->getSerialized());
        $environment->addDataProvider('current', $dataProvider);
        $environment->addDataProvider('parent-list', $parentDataProvider);
        $basicDefinition->setDataProvider('current');
        $basicDefinition->setParentDataProvider('parent-list');
        $basicDefinition->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
        $additionalFilter  = ['current' => 'foo'];
        $basicDefinition->setAdditionalFilter('current', $additionalFilter);
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig(null));
        self::assertIsArray($dataProviderConfig->getFilter());
        self::assertSame($additionalFilter, $dataProviderConfig->getFilter());
    }
}
