<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidateCacheTags;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent;
use FOS\HttpCache\CacheInvalidator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(InvalidateCacheTags::class)]
#[CoversClass(InvalidHttpCacheTagsEvent::class)]
#[CoversClass(ModelCollector::class)]
final class InvalidateCacheTagsTest extends TestCase
{
    public function testCacheManagerNotAvailable(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects($this->never())
            ->method('getDataDefinition');

        $model = $this->createMock(ModelInterface::class);
        $model
            ->expects($this->never())
            ->method('getId');
        $model
            ->expects($this->never())
            ->method('getProviderName');

        $invalidCacheTags = new InvalidateCacheTags('namespace.', $dispatcher);
        $invalidCacheTags->purgeCacheTags($model, $environment);
    }

    public function testPurgeHttpCacheWithNoParentRelation(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects($this->exactly(2))
            ->method('getParentDataDefinition')
            ->willReturn(null);

        $model1 = $this->createMock(ModelInterface::class);
        $model1
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $model1
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('foo');

        $model2 = $this->createMock(ModelInterface::class);
        $model2
            ->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $model2
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('bar');

        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects($this->exactly(2))
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags, $cacheManager) {
                    $actualInvalidTags = $invalidTags;
                    return $cacheManager;
                }
            );

        $invalidCacheTags = new InvalidateCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->purgeCacheTags($model1, $environment);
        self::assertSame(['namespace.foo', 'namespace.foo.1'], $actualInvalidTags);

        // Run the test in second time. For test are the tags from the first model not should be stayed.
        $invalidCacheTags->purgeCacheTags($model2, $environment);
        self::assertSame(['namespace.bar', 'namespace.bar.2'], $actualInvalidTags);
    }

    public function testPurgeHttpCacheWithAddingTagsFromTheEvent(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                function (InvalidHttpCacheTagsEvent $event) {
                    $event->setTags(
                        \array_merge(
                            $event->getTags(),
                            ['namespace.foo', 'namespace.foo.2']
                        )
                    );
                }
            );

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects($this->once())
            ->method('getParentDataDefinition')
            ->willReturn(null);

        $model = $this->createMock(ModelInterface::class);
        $model
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $model
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('foo');

        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags, $cacheManager) {
                    $actualInvalidTags = $invalidTags;
                    return $cacheManager;
                }
            );

        $invalidCacheTags = new InvalidateCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->purgeCacheTags($model, $environment);
        self::assertSame(['namespace.foo', 'namespace.foo.1', 'namespace.foo.2'], $actualInvalidTags);
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function testPurgeHttpCacheWithParentNotHierarchical(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $model1 = $this->createMock(ModelInterface::class);
        $model1
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $model1
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('foo');

        $model2 = $this->createMock(ModelInterface::class);
        $model2
            ->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $model2
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('bar');

        $dataProvider = $this->createMock(DataProviderInterface::class);
        $dataProvider
            ->expects($this->once())
            ->method('getEmptyConfig')
            ->willReturn(DefaultConfig::init());
        $dataProvider
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($model2);

        $parentChildCondition = $this->createMock(ParentChildConditionInterface::class);
        $parentChildCondition
            ->expects($this->once())
            ->method('getInverseFilterFor')
            ->with($model1)
            ->willReturn(['filter for get the parent model']);

        $relationships = $this->createMock(ModelRelationshipDefinitionInterface::class);
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('bar', 'foo')
            ->willReturn($parentChildCondition);

        $basicDefinition = $this->createMock(BasicDefinitionInterface::class);
        $basicDefinition
            ->expects($this->exactly(2))
            ->method('getMode')
            ->willReturn(1);
        $basicDefinition
            ->expects($this->once())
            ->method('getDataProvider')
            ->willReturn('foo');
        $basicDefinition
            ->expects($this->once())
            ->method('getParentDataProvider')
            ->willReturn('bar');

        $dataDefinition = $this->createMock(ContainerInterface::class);
        $dataDefinition
            ->expects($this->once())
            ->method('getModelRelationshipDefinition')
            ->willReturn($relationships);
        $dataDefinition
            ->expects($this->exactly(2))
            ->method('getBasicDefinition')
            ->willReturn($basicDefinition);

        $parentDataDefinition = $this->createMock(ContainerInterface::class);

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects($this->once())
            ->method('getParentDataDefinition')
            ->willReturn($parentDataDefinition);
        $environment
            ->expects($this->exactly(2))
            ->method('getDataDefinition')
            ->willReturn($dataDefinition);

        $environment
            ->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($dataProvider);


        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturnCallback(
                static function (array $invalidTags) use (&$actualInvalidTags, $cacheManager) {
                    $actualInvalidTags = $invalidTags;
                    return $cacheManager;
                }
            );

        $invalidCacheTags = new InvalidateCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->purgeCacheTags($model1, $environment);
        self::assertSame(['namespace.foo', 'namespace.foo.1', 'namespace.bar', 'namespace.bar.2'], $actualInvalidTags);
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function testPurgeHttpCacheWithParentHierarchical(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $model1 = $this->createMock(ModelInterface::class);
        $model1
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $model1
            ->expects($this->exactly(2))
            ->method('getProviderName')
            ->willReturn('foo');

        $model2 = $this->createMock(ModelInterface::class);
        $model2
            ->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $model2
            ->expects($this->once())
            ->method('getProviderName')
            ->willReturn('bar');

        $rootDataProvider = $this->createMock(DataProviderInterface::class);

        $parentDataProvider = $this->createMock(DataProviderInterface::class);
        $parentDataProvider
            ->expects($this->once())
            ->method('getEmptyConfig')
            ->willReturn(DefaultConfig::init());
        $parentDataProvider
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($model2);

        $rootCondition = $this->createMock(RootConditionInterface::class);

        $parentChildCondition = $this->createMock(ParentChildConditionInterface::class);
        $parentChildCondition
            ->expects($this->once())
            ->method('getInverseFilterFor')
            ->with($model1)
            ->willReturn(['filter for get the parent model']);

        $relationships = $this->createMock(ModelRelationshipDefinitionInterface::class);
        $relationships
            ->expects($this->once())
            ->method('getRootCondition')
            ->willReturn($rootCondition);
        $relationships
            ->expects($this->once())
            ->method('getChildCondition')
            ->with('bar', 'foo')
            ->willReturn($parentChildCondition);

        $basicDefinition = $this->createMock(BasicDefinitionInterface::class);
        $basicDefinition
            ->expects($this->exactly(2))
            ->method('getMode')
            ->willReturn(2);
        $basicDefinition
            ->expects($this->once())
            ->method('getDataProvider')
            ->willReturn('foo');
        $basicDefinition
            ->expects($this->once())
            ->method('getRootDataProvider')
            ->willReturn('foo');
        $basicDefinition
            ->expects($this->once())
            ->method('getParentDataProvider')
            ->willReturn('bar');

        $dataDefinition = $this->createMock(ContainerInterface::class);
        $dataDefinition
            ->expects($this->once())
            ->method('getModelRelationshipDefinition')
            ->willReturn($relationships);
        $dataDefinition
            ->expects($this->exactly(2))
            ->method('getBasicDefinition')
            ->willReturn($basicDefinition);

        $parentDataDefinition = $this->createMock(ContainerInterface::class);

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects($this->exactly(2))
            ->method('getParentDataDefinition')
            ->willReturn($parentDataDefinition);
        $environment
            ->expects($this->exactly(2))
            ->method('getDataDefinition')
            ->willReturn($dataDefinition);

        $environment
            ->expects($this->exactly(2))
            ->method('getDataProvider')
            ->willReturnCallback(
                static function (string $name) use ($rootDataProvider, $parentDataProvider) {
                    static $counter = 0;
                    switch ($counter++) {
                        case 0:
                            self::assertSame('foo', $name);
                            return $rootDataProvider;
                        case 1:
                            self::assertSame('bar', $name);
                            return $parentDataProvider;
                    }
                    self::fail('Unexpected call');
                }
            );

        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags, $cacheManager) {
                    $actualInvalidTags = $invalidTags;
                    return $cacheManager;
                }
            );

        $invalidCacheTags = new InvalidateCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->purgeCacheTags($model1, $environment);
        self::assertSame(['namespace.foo', 'namespace.foo.1', 'namespace.bar', 'namespace.bar.2'], $actualInvalidTags);
    }
}
