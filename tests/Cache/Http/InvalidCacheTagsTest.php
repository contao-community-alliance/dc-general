<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTags;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent;
use FOS\HttpCache\CacheInvalidator;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTags
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent
 */
class InvalidCacheTagsTest extends TestCase
{
    public function testCacheManagerNotAvailable(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::never())
            ->method('dispatch');

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects(self::never())
            ->method('getDataDefinition');

        $model = $this->createMock(ModelInterface::class);
        $model
            ->expects(self::never())
            ->method('getId');
        $model
            ->expects(self::never())
            ->method('getProviderName');

        $invalidCacheTags = new InvalidCacheTags('namespace.', $dispatcher);
        $invalidCacheTags->setEnvironment($environment);
        $invalidCacheTags->purgeCacheTags($model);
    }

    public function testPurgeHttpCacheWithNoParentRelation(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects(self::exactly(2))
            ->method('getParentDataDefinition')
            ->willReturn(null);

        $model1 = $this->createMock(ModelInterface::class);
        $model1
            ->expects(self::once())
            ->method('getId')
            ->willReturn(1);
        $model1
            ->expects(self::once())
            ->method('getProviderName')
            ->willReturn('foo');

        $model2 = $this->createMock(ModelInterface::class);
        $model2
            ->expects(self::once())
            ->method('getId')
            ->willReturn(2);
        $model2
            ->expects(self::once())
            ->method('getProviderName')
            ->willReturn('bar');

        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects(self::exactly(2))
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags) {
                    $actualInvalidTags = $invalidTags;
                }
            );

        $invalidCacheTags = new InvalidCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->setEnvironment($environment);
        $invalidCacheTags->purgeCacheTags($model1);
        self::assertSame(['namespace.foo', 'namespace.foo.1'], $actualInvalidTags);

        // Run the test in second time. For test are the tags from the first model not should be stayed.
        $invalidCacheTags->purgeCacheTags($model2);
        self::assertSame(['namespace.bar', 'namespace.bar.2'], $actualInvalidTags);
    }

    public function testPurgeHttpCacheWithAddingTagsFromTheEvent(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('getParentDataDefinition')
            ->willReturn(null);

        $model = $this->createMock(ModelInterface::class);
        $model
            ->expects(self::once())
            ->method('getId')
            ->willReturn(1);
        $model
            ->expects(self::once())
            ->method('getProviderName')
            ->willReturn('foo');

        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects(self::once())
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags) {
                    $actualInvalidTags = $invalidTags;
                }
            );

        $invalidCacheTags = new InvalidCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->setEnvironment($environment);
        $invalidCacheTags->purgeCacheTags($model);
        self::assertSame(['namespace.foo', 'namespace.foo.1', 'namespace.foo.2'], $actualInvalidTags);
    }

    public function testPurgeHttpCacheWithParentRelation(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $model1 = $this->createMock(ModelInterface::class);
        $model1
            ->expects(self::once())
            ->method('getId')
            ->willReturn(1);
        $model1
            ->expects(self::exactly(2))
            ->method('getProviderName')
            ->willReturn('foo');

        $model2 = $this->createMock(ModelInterface::class);
        $model2
            ->expects(self::once())
            ->method('getId')
            ->willReturn(2);
        $model2
            ->expects(self::once())
            ->method('getProviderName')
            ->willReturn('bar');

        $collection = $this->createMock(CollectionInterface::class);
        $collection
            ->expects(self::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$model2]));

        $dataProvider = $this->createMock(DataProviderInterface::class);
        $dataProvider
            ->expects(self::once())
            ->method('getEmptyConfig')
            ->willReturn(DefaultConfig::init());
        $dataProvider
            ->expects(self::once())
            ->method('fetchAll')
            ->willReturn($collection);

        $parentChildCondition = $this->createMock(ParentChildConditionInterface::class);
        $parentChildCondition
            ->expects(self::once())
            ->method('getInverseFilterFor')
            ->withConsecutive([$model1])
            ->willReturn(['filter for get the parent model']);

        $relationships = $this->createMock(ModelRelationshipDefinitionInterface::class);
        $relationships
            ->expects(self::once())
            ->method('getChildCondition')
            ->withConsecutive(['bar', 'foo'])
            ->willReturn($parentChildCondition);

        $dataDefinition = $this->createMock(ContainerInterface::class);
        $dataDefinition
            ->expects(self::once())
            ->method('getDefinition')
            ->willReturn($relationships);

        $parentDataDefinition = $this->createMock(ContainerInterface::class);
        $parentDataDefinition
            ->expects(self::exactly(2))
            ->method('getName')
            ->willReturn('bar');

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment
            ->expects(self::once())
            ->method('getParentDataDefinition')
            ->willReturn($parentDataDefinition);
        $environment
            ->expects(self::once())
            ->method('getDataDefinition')
            ->willReturn($dataDefinition);

        $environment
            ->expects(self::once())
            ->method('getDataProvider')
            ->willReturn($dataProvider);


        $actualInvalidTags = [];
        $cacheManager      = $this->createMock(CacheInvalidator::class);
        $cacheManager
            ->expects(self::once())
            ->method('invalidateTags')
            ->willReturnCallback(
                function (array $invalidTags) use (&$actualInvalidTags) {
                    $actualInvalidTags = $invalidTags;
                }
            );

        $invalidCacheTags = new InvalidCacheTags('namespace.', $dispatcher, $cacheManager);
        $invalidCacheTags->setEnvironment($environment);
        $invalidCacheTags->purgeCacheTags($model1);
        self::assertSame(['namespace.foo', 'namespace.foo.1', 'namespace.bar', 'namespace.bar.2'], $actualInvalidTags);
    }
}
