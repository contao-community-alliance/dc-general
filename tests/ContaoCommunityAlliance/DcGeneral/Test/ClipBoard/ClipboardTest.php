<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
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
}
