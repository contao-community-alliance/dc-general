<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the clipboard.
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

        $this->assertTrue($clipboard->isEmpty($filterGetAll));
        $this->assertFalse($clipboard->isNotEmpty($filterGetAll));

        $clipboard->push($createItem);
        $this->assertFalse($clipboard->isEmpty($filterGetAll));
        $this->assertTrue($clipboard->isNotEmpty($filterGetAll));

        $this->assertTrue($clipboard->has($createItem));
        $this->assertTrue($clipboard->has(clone $createItem));

        $clipboard2 = new Clipboard();
        $clipboard->saveTo($environment);
        $clipboard2->loadFrom($environment);

        $this->assertTrue($clipboard2->has($createItem));
        $this->assertTrue($clipboard2->has(clone $createItem));
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

        $this->assertTrue($clipboard->hasId($modelId));

        $items = $clipboard->fetch($filterGetAll);

        $this->assertCount(2, $items);
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

        $this->assertTrue($clipboard->hasId($modelId));
        $this->assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        $this->assertCount(2, $items);
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

        $this->assertFalse($clipboard->hasId($modelId));
        $this->assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        $this->assertCount(1, $items);
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

        $this->assertTrue($clipboard->hasId($modelId));
        $this->assertTrue($clipboard->hasId($otherModelId));

        $items = $clipboard->fetch($filterGetAll);

        $this->assertCount(2, $items);
        $this->assertArraySubset([$cutItem, $copyOtherItem], $items);
    }
}
