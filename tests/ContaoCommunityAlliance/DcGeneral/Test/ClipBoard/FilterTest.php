<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
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
        return array(
            array(ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT),
            array(ItemInterface::COPY, ItemInterface::CREATE, ItemInterface::DEEP_COPY),
            array(ItemInterface::DEEP_COPY, ItemInterface::COPY, ItemInterface::CREATE),
            array(ItemInterface::CREATE, ItemInterface::COPY, ItemInterface::CUT),
            array(ItemInterface::CUT, ItemInterface::COPY, ItemInterface::CREATE),
        );
    }

    /**
     * Test andActionIs filter.
     *
     * @dataProvider provideActions()
     */
    public function testAndActionIs($action1, $action2)
    {
        $filter = new Filter();
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
        $filter->andActionIsIn(array($action1));

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
        $filter->andActionIsNotIn(array($action1, $action2));

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
        $filter->andActionIsIn(array($action1));

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
            ->orActionIsNotIn(array($action1))
            ->orActionIsNotIn(array($action1, $action2));

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

        return array(
            array(true, $parentId1, $parentId1, $parentId3),
            array(false, $parentId1, $parentId2, $parentId3),
            array(false, null, $parentId2, $parentId3),
        );
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

        return array(
            array(true, $modelId1, $modelId1),
            array(false, $modelId1, $modelId2),
            array(false, $modelId2, $modelId1),
        );
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

        return array(
            array(false, $modelId1, $modelId1),
            array(true, $modelId1, $modelId2),
            array(true, $modelId2, $modelId1),
        );
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

        return array(
            array(true, $modelId1, $modelId1, $modelId2),
            array(false, $modelId1, $modelId2, $modelId3),
            array(true, $modelId1, $modelId1, $modelId3),
        );
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

        return array(
            array(true, $modelId1, $modelId1, $modelId2),
            array(true, $modelId1, $modelId2, $modelId3),
            array(false, $modelId1, $modelId1, $modelId1),
        );
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

        return array(
            array(true, $provider1, $provider1),
            array(false, $provider1, $provider2),
            array(true, $provider2, $provider2),
        );
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

        return array(
            array(false, $provider1, $provider1),
            array(true, $provider1, $provider2),
            array(false, $provider2, $provider2),
        );
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

        $filter->andModelIsNotFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }


    /**
     * Test andModelIs filter.
     *
     * @dataProvider provideForModelIsNotFromDataProvider()
     */
    public function testParentIdIsNotFromDataProvider($expected, $provider1, $provider2)
    {
        $filter = new Filter();
        $item   = new MockedAbstractItem(ItemInterface::CREATE, new ModelId($provider1, 3), null);

        $filter->andParentIsNotFromProvider($provider2);

        $this->assertEquals($expected, $filter->accepts($item));
    }
}
