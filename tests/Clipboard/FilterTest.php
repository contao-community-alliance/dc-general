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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test for the Filter.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[CoversClass(Filter::class)]
final class FilterTest extends TestCase
{
    /**
     * Provide an action matrix with 3 different actions per row.
     */
    public static function provideTwoActions(): array
    {
        return [
            [ItemInterface::CREATE, ItemInterface::COPY],
            [ItemInterface::COPY, ItemInterface::CREATE],
            [ItemInterface::DEEP_COPY, ItemInterface::COPY],
            [ItemInterface::CREATE, ItemInterface::COPY],
            [ItemInterface::CUT, ItemInterface::COPY],
        ];
    }

    #[Dataprovider('provideTwoActions')]
    public function testAndActionIs($action1, $action2): void
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIs($action1);

        $item = new MockedAbstractItem($action1);
        self::assertTrue($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertFalse($filter->accepts($item2));
    }

    #[Dataprovider('provideTwoActions')]
    public function testAndActionIsNot($action1, $action2): void
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsNot($action1);

        $item = new MockedAbstractItem($action1);
        self::assertFalse($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertTrue($filter->accepts($item2));
    }

    /**
     * Provide an action matrix with 3 different actions per row.
     */
    public static function provideActions(): array
    {
        return [
            [ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT],
            [ItemInterface::COPY, ItemInterface::CREATE, ItemInterface::DEEP_COPY],
            [ItemInterface::DEEP_COPY, ItemInterface::COPY, ItemInterface::CREATE],
            [ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT],
            [ItemInterface::CUT, ItemInterface::COPY, ItemInterface::CREATE],
        ];
    }
    #[Dataprovider('provideActions')]
    public function testOrActionIs($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter->orSub(new MockedFilter(false));
        $filter->orActionIs($action1)->orActionIs($action2);

        $item = new MockedAbstractItem($action1);
        self::assertTrue($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertTrue($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertFalse($filter->accepts($item3));
    }

    #[Dataprovider('provideActions')]
    public function testOrActionIsNot($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(false));
        $filter->orActionIsNot($action1);

        $item = new MockedAbstractItem($action1);
        self::assertFalse($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertTrue($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertTrue($filter->accepts($item3));
    }

    #[Dataprovider('provideActions')]
    public function testAndActionIsIn($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsIn([$action1]);

        $item = new MockedAbstractItem($action1);
        self::assertTrue($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertFalse($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertFalse($filter->accepts($item3));
    }

    #[Dataprovider('provideActions')]
    public function testAndActionIsNotIn($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsNotIn([$action1, $action2]);

        $item = new MockedAbstractItem($action1);
        self::assertFalse($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertFalse($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertTrue($filter->accepts($item3));
    }

    #[Dataprovider('provideActions')]
    public function testOrActionIsIn($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter->orSub(new MockedFilter(false));
        $filter->orActionIsIn([$action1]);

        $item = new MockedAbstractItem($action1);
        self::assertTrue($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertFalse($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertFalse($filter->accepts($item3));
    }

    #[Dataprovider('provideActions')]
    public function testOrActionIsNotIn($action1, $action2, $action3): void
    {
        $filter = new Filter();
        $filter
            ->andSub(new MockedFilter(false))
            ->orActionIsNotIn([$action1])
            ->orActionIsNotIn([$action1, $action2]);

        $item = new MockedAbstractItem($action1);
        self::assertFalse($filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        self::assertTrue($filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        self::assertTrue($filter->accepts($item3));
    }

    public function testAndHasNoParent(): void
    {
        $filter = new Filter();

        $parentId = new ModelId('dummy-provider', 5);
        $item     = new MockedAbstractItem(ItemInterface::CREATE, $parentId);
        $item2    = new MockedAbstractItem(ItemInterface::CREATE);

        $filter->andSub(new MockedFilter(true));
        $filter->andHasNoParent();

        self::assertFalse($filter->accepts($item));
        self::assertTrue($filter->accepts($item2));
    }

    public function testOrHasNoParent(): void
    {
        $filter = new Filter();

        $parentId = new ModelId('dummy-provider', 5);
        $item     = new MockedAbstractItem(ItemInterface::CREATE, $parentId);
        $item2    = new MockedAbstractItem(ItemInterface::CREATE);

        $filter->andSub(new MockedFilter(false));
        $filter->orHasNoParent();

        self::assertFalse($filter->accepts($item));
        self::assertTrue($filter->accepts($item2));
    }

    public static function provideParentsForOr(): array
    {
        $parentId1 = new ModelId('dummy-provider', 4);
        $parentId2 = new ModelId('dummy-provider', 5);
        $parentId3 = new ModelId('dummy-provider', 6);

        return [
            [true, $parentId1, $parentId1, $parentId3],
            [false, $parentId1, $parentId2, $parentId3],
            [false, null, $parentId2, $parentId3],
        ];
    }

    #[Dataprovider('provideParentsForOr')]
    public function testOrParentIs($expected, $parentId1, $parentId2, $parentId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIs($parentId2)->orParentIs($parentId3);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideParentsForAnd(): array
    {
        $parentId1 = new ModelId('dummy-provider', 4);
        $parentId2 = new ModelId('dummy-provider', 5);

        return [
            [true, $parentId1, $parentId1],
            [false, $parentId1, $parentId2],
            [false, null, $parentId2],
        ];
    }

    #[Dataprovider('provideParentsForAnd')]
    public function testAndParentIs($expected, $parentId1, $parentId2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIs($parentId2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForAndModelIdIs(): array
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);

        return [
            [true, $modelId1, $modelId1],
            [false, $modelId1, $modelId2],
            [false, $modelId2, $modelId1],
        ];
    }

    #[Dataprovider('provideForAndModelIdIs')]
    public function testAndModelIdIs($expected, $modelId1, $modelId2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIs($modelId2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForAndModelIdIsNot(): array
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);

        return [
            [false, $modelId1, $modelId1],
            [true, $modelId1, $modelId2],
            [true, $modelId2, $modelId1],
        ];
    }

    #[Dataprovider('provideForAndModelIdIsNot')]
    public function testAndModelIdIsNot($expected, $modelId1, $modelId2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsNot($modelId2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForOrModelIdIs(): array
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);
        $modelId3 = new ModelId('dummy-provider', 5);

        return [
            [true, $modelId1, $modelId1, $modelId2],
            [false, $modelId1, $modelId2, $modelId3],
            [true, $modelId1, $modelId1, $modelId3],
        ];
    }

    #[Dataprovider('provideForOrModelIdIs')]
    public function testOrModelIdIs($expected, $modelId1, $modelId2, $modelId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orModelIs($modelId2)->orModelIs($modelId3);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForOrModelIdIsNot(): array
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);
        $modelId3 = new ModelId('dummy-provider', 5);

        return [
            [true, $modelId1, $modelId1, $modelId2],
            [true, $modelId1, $modelId2, $modelId3],
            [false, $modelId1, $modelId1, $modelId1],
        ];
    }

    #[Dataprovider('provideForOrModelIdIsNot')]
    public function testOrModelIdIsNot($expected, $modelId1, $modelId2, $modelId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orModelIsNot($modelId2)->orModelIsNot($modelId3);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForModelIsFromDataProvider(): array
    {
        $provider1 = 'dummy-a';
        $provider2 = 'dummy-b';

        return [
            [true, $provider1, $provider1],
            [false, $provider1, $provider2],
            [true, $provider2, $provider2],
        ];
    }

    #[Dataprovider('provideForModelIsFromDataProvider')]
    public function testModelIsFromDataProvider($expected, $provider1, $provider2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $provider1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsFromProvider($provider2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideForModelIsFromDataProvider')]
    public function testParentIdIsFromDataProvider($expected, $provider1, $provider2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsFromProvider($provider2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideForModelIsNotFromDataProvider(): array
    {
        $provider1 = 'dummy-a';
        $provider2 = 'dummy-b';

        return [
            [false, $provider1, $provider1],
            [true, $provider1, $provider2],
            [false, $provider2, $provider2],
        ];
    }

    #[Dataprovider('provideForModelIsNotFromDataProvider')]
    public function testModelIsNotFromDataProvider($expected, $provider1, $provider2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $provider1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsNotFromProvider($provider2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideForModelIsNotFromDataProvider')]
    public function testParentIdIsNotFromDataProvider($expected, $provider1, $provider2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsNotFromProvider($provider2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideForModelIsNotFromDataProvider')]
    public function testOrParentIsNotFromProvider($expected, $provider1, $provider2): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIsNotFromProvider($provider2);

        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideParentsForOr')]
    public function testAndParentIsIn($expected, $parentId1, $parentId2, $parentId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsIn([$parentId2, $parentId3]);
        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideParentsForOr')]
    public function testParentIsNotIn($expected, $parentId1, $parentId2, $parentId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsNotIn([$parentId2, $parentId3]);
        self::assertEquals(!$expected, $filter->accepts($item));
    }

    #[Dataprovider('provideParentsForOr')]
    public function testOrParentIsIn($expected, $parentId1, $parentId2, $parentId3): void
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIsIn([$parentId2, $parentId3]);
        self::assertEquals($expected, $filter->accepts($item));
    }

    public static function provideSubFilter(): array
    {
        return [
            [true, new MockedFilter(true)],
            [false, new MockedFilter(false)]
        ];
    }

    #[Dataprovider('provideSubFilter')]
    public function testAndSub($expected, FilterInterface $subFilter): void
    {
        $item   = new MockedAbstractItem(ItemInterface::CREATE);

        $firstSub = new MockedFilter(true);
        $filter   = new Filter();

        $filter->andSub($firstSub);
        $filter->andSub($subFilter);

        self::assertEquals($expected, $filter->accepts($item));
    }

    #[Dataprovider('provideSubFilter')]
    public function testOrSub($expected, FilterInterface $subFilter): void
    {
        $item   = new MockedAbstractItem(ItemInterface::CREATE);

        $firstSub = new MockedFilter(false);
        $filter   = new Filter();

        $filter->orSub($firstSub);
        $filter->orSub($subFilter);

        self::assertEquals($expected, $filter->accepts($item));
    }
}
