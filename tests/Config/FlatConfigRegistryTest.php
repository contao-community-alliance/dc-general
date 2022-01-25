<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Config;

use ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Config\FlatConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\EnvironmentFlatConfigRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\Config\FlatConfigRegistry
 */
class FlatConfigRegistryTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $environment = $this->getMockForAbstractClass(EnvironmentFlatConfigRegistryInterface::class);

        $configRegistry = new FlatConfigRegistry();

        self::assertNull($configRegistry->getEnvironment());
        self::assertInstanceOf(BaseConfigRegistryInterface::class, $configRegistry->setEnvironment($environment));
        self::assertInstanceOf(EnvironmentFlatConfigRegistryInterface::class, $configRegistry->getEnvironment());
        self::assertSame($environment, $configRegistry->getEnvironment());
    }

    public function testGetBaseConfig(): void
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

        $viewDefinition->method('getListingConfig')->willReturn($listingConfig);

        $definition = [
            Contao2BackendViewDefinitionInterface::NAME => $viewDefinition
        ];
        $dataDefinition->method('hasDefinition')->willReturnCallback(
            function ($definitionName) use ($definition) {
                return array_key_exists($definitionName, $definition);
            }
        );
        $dataDefinition->method('getDefinition')->willReturnCallback(
            function ($definitionName) use ($definition) {
                return $definition[$definitionName];
            }
        );
        $dataDefinition->method('getModelRelationshipDefinition')->willReturn($modelRelationShip);

        $environment->method('getDataDefinition')->willReturn($dataDefinition);

        $configRegistry = new FlatConfigRegistry();
        $configRegistry->setEnvironment($environment);

        // Single data provider test settings.
        $dataDefinition->method('getBasicDefinition')->willReturn($basicDefinition);
        $singleDataProvider       = $this->createMock(DefaultDataProvider::class);
        $singleDataProviderConfig = DefaultConfig::init();
        $singleDataProvider->method('getEmptyConfig')->willReturn($singleDataProviderConfig);
        $singleAdditionalFilter  = ['single' => 'foo'];

        // Single data provider tests.
        $environment->addDataProvider('single', $singleDataProvider);
        $basicDefinition->setDataProvider('single');
        $basicDefinition->setAdditionalFilter('single', ['single' => 'foo']);
        self::assertInstanceOf(ConfigInterface::class, $configRegistry->getBaseConfig());
        self::assertIsArray($singleDataProviderConfig->getFilter());
        self::assertSame($singleAdditionalFilter, $singleDataProviderConfig->getFilter());
    }
}
