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

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * Test for the Filter.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\ClipBoard
 */
class FilterTest extends TestCase
{
    /**
     * Provide an action matrix with 3 different actions per row.
     *
     * @return array
     */
    public function provideActions()
    {
        return [
            [ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT],
            [ItemInterface::COPY, ItemInterface::CREATE, ItemInterface::DEEP_COPY],
            [ItemInterface::DEEP_COPY, ItemInterface::COPY, ItemInterface::CREATE],
            [ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT],
            [ItemInterface::CUT, ItemInterface::COPY, ItemInterface::CREATE],
        ];
    }

    /**
     * Test andActionIs filter.
     *
     * @dataProvider provideActions()
     */
    public function testAndActionIs($action1, $action2)
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIs($action1);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(true, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(false, $filter->accepts($item2));
    }

    /**
     * Test andActionIsNot filter.
     *
     * @dataProvider provideActions()
     */
    public function testAndActionIsNot($action1, $action2)
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsNot($action1);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(false, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(true, $filter->accepts($item2));
    }

    /**
     * Test orActionIs filter.
     *
     * @dataProvider provideActions()
     */
    public function testOrActionIs($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter->orSub(new MockedFilter(false));
        $filter->orActionIs($action1)->orActionIs($action2);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(true, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(true, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(false, $filter->accepts($item3));
    }

    /**
     * Test orActionIsNot filter.
     *
     * @dataProvider provideActions()
     */
    public function testOrActionIsNot($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(false));
        $filter->orActionIsNot($action1);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(false, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(true, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(true, $filter->accepts($item3));
    }

    /**
     * Test andActionIsIn filter.
     *
     * @dataProvider provideActions()
     */
    public function testAndActionIsIn($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsIn([$action1]);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(true, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(false, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(false, $filter->accepts($item3));
    }

    /**
     * Test andActionIsNotIn filter.
     *
     * @dataProvider provideActions()
     */
    public function testAndActionIsNotIn($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter->andSub(new MockedFilter(true));
        $filter->andActionIsNotIn([$action1, $action2]);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(false, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(false, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(true, $filter->accepts($item3));
    }

    /**
     * Test andActionIsIn filter.
     *
     * @dataProvider provideActions()
     */
    public function testOrActionIsIn($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter->orSub(new MockedFilter(false));
        $filter->orActionIsIn([$action1]);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(true, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(false, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(false, $filter->accepts($item3));
    }

    /**
     * Test orActionIsNotIn filter.
     *
     * @dataProvider provideActions()
     */
    public function testOrActionIsNotIn($action1, $action2, $action3)
    {
        $filter = new Filter();
        $filter
            ->andSub(new MockedFilter(false))
            ->orActionIsNotIn([$action1])
            ->orActionIsNotIn([$action1, $action2]);

        $item = new MockedAbstractItem($action1);
        $this->assertEquals(false, $filter->accepts($item));

        $item2 = new MockedAbstractItem($action2);
        $this->assertEquals(true, $filter->accepts($item2));

        $item3 = new MockedAbstractItem($action3);
        $this->assertEquals(true, $filter->accepts($item3));
    }

    /**
     * Test andHasNoParent filter.
     */
    public function testAndHasNoParent()
    {
        $filter = new Filter();

        $parentId = new ModelId('dummy-provider', 5);
        $item     = new MockedAbstractItem(ItemInterface::CREATE, $parentId);
        $item2    = new MockedAbstractItem(ItemInterface::CREATE);

        $filter->andSub(new MockedFilter(true));
        $filter->andHasNoParent();

        $this->assertEquals(false, $filter->accepts($item));
        $this->assertEquals(true, $filter->accepts($item2));
    }

    /**
     * Test orHasNoParent filter.
     */
    public function testOrHasNoParent()
    {
        $filter = new Filter();

        $parentId = new ModelId('dummy-provider', 5);
        $item     = new MockedAbstractItem(ItemInterface::CREATE, $parentId);
        $item2    = new MockedAbstractItem(ItemInterface::CREATE);

        $filter->andSub(new MockedFilter(false));
        $filter->orHasNoParent();

        $this->assertEquals(false, $filter->accepts($item));
        $this->assertEquals(true, $filter->accepts($item2));
    }

    /**
     * Provide test matrix for testOrParentIs and testAndParentIs.
     *
     * @return array
     */
    public function provideParents()
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

    /**
     * Test orParentIs filter.
     *
     * @dataProvider provideParents()
     */
    public function testOrParentIs($expected, $parentId1, $parentId2, $parentId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIs($parentId2)->orParentIs($parentId3);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test orParentIs filter.
     *
     * @dataProvider provideParents()
     */
    public function testAndParentIs($expected, $parentId1, $parentId2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIs($parentId2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testAndModelIdIs.
     *
     * @return array
     */
    public function provideForAndModelIdIs()
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);

        return [
            [true, $modelId1, $modelId1],
            [false, $modelId1, $modelId2],
            [false, $modelId2, $modelId1],
        ];
    }

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForAndModelIdIs()
     */
    public function testAndModelIdIs($expected, $modelId1, $modelId2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIs($modelId2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testAndModelIdIsNot.
     *
     * @return array
     */
    public function provideForAndModelIdIsNot()
    {
        $modelId1 = new ModelId('dummy-provider', 4);
        $modelId2 = new ModelId('dummy-provider', 5);

        return [
            [false, $modelId1, $modelId1],
            [true, $modelId1, $modelId2],
            [true, $modelId2, $modelId1],
        ];
    }

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForAndModelIdIsNot()
     */
    public function testAndModelIdIsNot($expected, $modelId1, $modelId2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsNot($modelId2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testOrModelIdIs.
     *
     * @return array
     */
    public function provideForOrModelIdIs()
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

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForOrModelIdIs()
     */
    public function testOrModelIdIs($expected, $modelId1, $modelId2, $modelId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orModelIs($modelId2)->orModelIs($modelId3);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testOrModelIdIsNot.
     *
     * @return array
     */
    public function provideForOrModelIdIsNot()
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

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForOrModelIdIsNot()
     */
    public function testOrModelIdIsNot($expected, $modelId1, $modelId2, $modelId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $modelId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orModelIsNot($modelId2)->orModelIsNot($modelId3);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testForModelIsFromDataProvider.
     *
     * @return array
     */
    public function provideForModelIsFromDataProvider()
    {
        $provider1 = 'dummy-a';
        $provider2 = 'dummy-b';

        return [
            [true, $provider1, $provider1],
            [false, $provider1, $provider2],
            [true, $provider2, $provider2],
        ];
    }

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForModelIsFromDataProvider()
     */
    public function testModelIsFromDataProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $provider1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForModelIsFromDataProvider()
     */
    public function testParentIdIsFromDataProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide test matrix for testModelIsNotFromDataProvider.
     *
     * @return array
     */
    public function provideForModelIsNotFromDataProvider()
    {
        $provider1 = 'dummy-a';
        $provider2 = 'dummy-b';

        return [
            [false, $provider1, $provider1],
            [true, $provider1, $provider2],
            [false, $provider2, $provider2],
        ];
    }

    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForModelIsNotFromDataProvider()
     */
    public function testModelIsNotFromDataProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, null, $provider1);

        $filter->andSub(new MockedFilter(true));
        $filter->andModelIsNotFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }


    /**
     * Test andParentIsNotFromProvider filter.
     *
     * @dataProvider provideForModelIsNotFromDataProvider()
     */
    public function testParentIdIsNotFromDataProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsNotFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test orParentIsNotFromProvider filter.
     *
     * @dataProvider provideForModelIsNotFromDataProvider()
     */
    public function testOrParentIsNotFromProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIsNotFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test testAndParentIsIn filter.
     *
     * @dataProvider provideParents()
     */
    public function testAndParentIsIn($expected, $parentId1, $parentId2, $parentId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsIn([$parentId2, $parentId3]);
        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test testAndParentIsIn filter.
     *
     * @dataProvider provideParents()
     */
    public function testParentIsNotIn($expected, $parentId1, $parentId2, $parentId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(true));
        $filter->andParentIsNotIn([$parentId2, $parentId3]);
        $this->assertEquals(!$expected, $filter->accepts($item));
    }

    /**
     * Test testAndParentIsIn filter.
     *
     * @dataProvider provideParents()
     */
    public function testOrParentIsIn($expected, $parentId1, $parentId2, $parentId3)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, $parentId1);

        $filter->andSub(new MockedFilter(false));
        $filter->orParentIsIn([$parentId2, $parentId3]);
        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Provide sub filter test values.
     *
     * @return array
     */
    public function provideSubFilter()
    {
        return [
            [true, new MockedFilter(true)],
            [false, new MockedFilter(false)]
        ];
    }

    /**
     * Test and sub filter.
     *
     * @dataProvider provideSubFilter()
     */
    public function testAndSub($expected, FilterInterface $subFilter)
    {
        $item   = new MockedAbstractItem(ItemInterface::CREATE);

        $firstSub = new MockedFilter(true);
        $filter   = new Filter();

        $filter->andSub($firstSub);
        $filter->andSub($subFilter);

        $this->assertEquals($expected, $filter->accepts($item));
    }

    /**
     * Test and sub filter.
     *
     * @dataProvider provideSubFilter()
     */
    public function testOrSub($expected, FilterInterface $subFilter)
    {
        $item   = new MockedAbstractItem(ItemInterface::CREATE);

        $firstSub = new MockedFilter(false);
        $filter   = new Filter();

        $filter->orSub($firstSub);
        $filter->orSub($subFilter);

        $this->assertEquals($expected, $filter->accepts($item));
    }
}
