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

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use PHPUnit\Framework\TestCase;

/**
 * The clipboard unsaved item test.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem
 */
class UnsavedItemTest extends TestCase
{
    public function dataNotProvideAction()
    {
        return [
            [ItemInterface::COPY],
            [ItemInterface::CUT],
            [ItemInterface::DEEP_COPY]
        ];
    }

    /**
     * @dataProvider dataNotProvideAction
     */
    public function testNotProvideAction($action)
    {
        try {
            new UnsavedItem($action, null, 'non');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertSame('UnsavedItem is designed for create actions only.', $exception->getMessage());
        }
    }

    public function dataTestGetter()
    {
        $modelId = ModelId::fromValues('parent', 'foo');
        return [
            [null, 'parent-null', 'parent-null', 'createparent-nullnull'],
            [$modelId, 'parent', 'parent', 'createparent'. $modelId->getSerialized()]
        ];
    }

    /**
     * @dataProvider dataTestGetter
     */
    public function testGetter($parentId, $providerName, $exceptedProviderName, $exceptedClipboardId)
    {
        $unsavedItem = new UnsavedItem(ItemInterface::CREATE, $parentId, $providerName);

        $this->assertNull($unsavedItem->getModelId());
        $this->assertIsString($unsavedItem->getDataProviderName());
        $this->assertSame($exceptedProviderName, $unsavedItem->getDataProviderName());
        $this->assertIsString($unsavedItem->getClipboardId());
        $this->assertSame($exceptedClipboardId, $unsavedItem->getClipboardId());
    }
}
