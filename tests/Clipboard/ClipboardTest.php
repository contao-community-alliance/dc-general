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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the clipboard.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard
 */
class ClipboardTest extends TestCase
{
    /**
     * Mocks the environment with session storage.
     *
     * @return DefaultEnvironment
     */
    private function mockEnvironment()
    {
        $environment = new DefaultEnvironment();
        $environment->setSessionStorage(new MockedSessionStorage());

        return $environment;
    }

    /**
     * Test various operations.
     *
     * @return void
     */
    public function testAll()
    {
        $environment = $this->mockEnvironment();

        $clipboard    = new Clipboard();
        $filterGetAll = new Filter();
        $createItem   = new UnsavedItem(Item::CREATE, null, 'dummy-provider');

        self::assertTrue($clipboard->isEmpty($filterGetAll));
        self::assertFalse($clipboard->isNotEmpty($filterGetAll));

        $clipboard->push($createItem);
        self::assertFalse($clipboard->isEmpty($filterGetAll));
        self::assertTrue($clipboard->isNotEmpty($filterGetAll));

        self::assertTrue($clipboard->has($createItem));
        self::assertTrue($clipboard->has(clone $createItem));

        $clipboard2 = new Clipboard();
        $clipboard->saveTo($environment);
        $clipboard2->loadFrom($environment);

        self::assertTrue($clipboard2->has($createItem));
        self::assertTrue($clipboard2->has(clone $createItem));
    }

    /**
     * Test that the same model may be stored multiple times.
     *
     * @return void
     */
    public function testAcceptsModelMultipleTimes()
    {
        $modelId      = ModelId::fromValues('dummy-provider', '15');
        $clipboard    = new Clipboard();
        $filterGetAll = new Filter();
        $cutItem      = new Item(Item::CUT, null, $modelId);
        $copyItem     = new Item(Item::COPY, null, $modelId);

        $clipboard->push($cutItem);
        $clipboard->push($copyItem);

        self::assertTrue($clipboard->hasId($modelId));

        $items = $clipboard->fetch($filterGetAll);

        self::assertCount(2, $items);
    }

    /**
     * Test removeByClipboardId() method.
     *
     * @return void
     */
    public function testRemoveByClipboardIdRemovesOnlyOneOfModel()
    {
        $modelId       = ModelId::fromValues('dummy-provider', '15');
        $otherModelId  = ModelId::fromValues('dummy-provider', '16');
        $clipboard     = new Clipboard();
        $filterGetAll  = new Filter();
        $cutItem       = new Item(Item::CUT, null, $modelId);
        $copyItem      = new Item(Item::COPY, null, $modelId);
        $copyOtherItem = new Item(Item::COPY, null, $otherModelId);

        $clipboard->push($cutItem);
        $clipboard->push($copyItem);
        $clipboard->push($copyOtherItem);

        $clipboard->removeByClipboardId($cutItem->getClipboardId());

        self::assertTrue($clipboard->hasId($modelId));
        self::assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        self::assertCount(2, $items);
    }

    /**
     * Test removeById() method.
     *
     * @return void
     */
    public function testRemoveByIdRemovesAllOfModel()
    {
        $modelId       = ModelId::fromValues('dummy-provider', '15');
        $otherModelId  = ModelId::fromValues('dummy-provider', '16');
        $clipboard     = new Clipboard();
        $filterGetAll  = new Filter();
        $cutItem       = new Item(Item::CUT, null, $modelId);
        $copyItem      = new Item(Item::COPY, null, $modelId);
        $copyOtherItem = new Item(Item::COPY, null, $otherModelId);

        $clipboard->push($cutItem);
        $clipboard->push($copyItem);
        $clipboard->push($copyOtherItem);

        $clipboard->removeById($modelId);

        self::assertFalse($clipboard->hasId($modelId));
        self::assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        self::assertCount(1, $items);
    }

    /**
     * Test remove() method.
     *
     * @return void
     */
    public function testRemoveRemovesOnlyOneOfModel()
    {
        $modelId       = ModelId::fromValues('dummy-provider', '15');
        $otherModelId  = ModelId::fromValues('dummy-provider', '16');
        $clipboard     = new Clipboard();
        $filterGetAll  = new Filter();
        $cutItem       = new Item(Item::CUT, null, $modelId);
        $copyItem      = new Item(Item::COPY, null, $modelId);
        $copyOtherItem = new Item(Item::COPY, null, $otherModelId);

        $clipboard->push($cutItem);
        $clipboard->push($copyItem);
        $clipboard->push($copyOtherItem);

        $clipboard->remove($copyItem);

        self::assertTrue($clipboard->hasId($modelId));
        self::assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        self::assertCount(2, $items);
        self::assertSame([$cutItem, $copyOtherItem], $items);
    }
}
