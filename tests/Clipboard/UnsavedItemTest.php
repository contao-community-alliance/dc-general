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
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The clipboard unsaved item test.
 */
#[CoversClass(UnsavedItem::class)]
final class UnsavedItemTest extends TestCase
{
    public static function dataNotProvideAction(): array
    {
        return [
            [ItemInterface::COPY],
            [ItemInterface::CUT],
            [ItemInterface::DEEP_COPY]
        ];
    }

    #[Dataprovider('dataNotProvideAction')]
    public function testNotProvideAction($action): void
    {
        try {
            new UnsavedItem($action, null, 'non');
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception);
            self::assertSame('UnsavedItem is designed for create actions only.', $exception->getMessage());
        }
    }

    public static function dataTestGetter(): array
    {
        $modelId = ModelId::fromValues('parent', 'foo');
        return [
            [null, 'parent-null', 'parent-null', 'createparent-nullnull'],
            [$modelId, 'parent', 'parent', 'createparent' . $modelId->getSerialized()]
        ];
    }

    #[Dataprovider('dataTestGetter')]
    public function testGetter($parentId, $providerName, $exceptedProviderName, $exceptedClipboardId): void
    {
        $unsavedItem = new UnsavedItem(ItemInterface::CREATE, $parentId, $providerName);

        self::assertNull($unsavedItem->getModelId());
        self::assertIsString($unsavedItem->getDataProviderName());
        self::assertSame($exceptedProviderName, $unsavedItem->getDataProviderName());
        self::assertIsString($unsavedItem->getClipboardId());
        self::assertSame($exceptedClipboardId, $unsavedItem->getClipboardId());
    }
}
