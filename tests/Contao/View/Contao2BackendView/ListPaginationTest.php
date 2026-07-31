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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ListPagination;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * This tests the pagination calculation.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) The edge cases are worth a test each.
 */
#[CoversClass(ListPagination::class)]
final class ListPaginationTest extends TestCase
{
    /**
     * Test the amount of pages.
     *
     * @param int $total    The total amount of records.
     * @param int $perPage  The amount per page.
     * @param int $expected The expected amount of pages.
     *
     * @return void
     */
    #[DataProvider('pageCountProvider')]
    public function testCountsPages(int $total, int $perPage, int $expected): void
    {
        self::assertSame($expected, (new ListPagination($total, $perPage))->getPageCount());
    }

    /**
     * Data provider for the amount of pages.
     *
     * @return array<string, array{int, int, int}>
     */
    public static function pageCountProvider(): array
    {
        return [
            'leer ergibt trotzdem eine Seite' => [0, 3, 1],
            'weniger als eine Seite'          => [2, 3, 1],
            'genau eine Seite'                => [3, 3, 1],
            'eine Seite plus Rest'            => [4, 3, 2],
            'glatt aufgehend'                 => [9, 3, 3],
            'ohne Begrenzung'                 => [50, 0, 1],
        ];
    }

    /**
     * Test that the offset determines the current page.
     *
     * @return void
     */
    public function testDeterminesCurrentPageFromOffset(): void
    {
        self::assertSame(1, (new ListPagination(9, 3, 0))->getCurrentPage());
        self::assertSame(2, (new ListPagination(9, 3, 3))->getCurrentPage());
        self::assertSame(3, (new ListPagination(9, 3, 6))->getCurrentPage());
    }

    /**
     * Test that an offset off the raster falls back to the page containing it.
     *
     * This happens after the amount per page has been changed while an offset was stored.
     *
     * @return void
     */
    public function testRoundsAnOffsetOffTheRasterDown(): void
    {
        self::assertSame(2, (new ListPagination(9, 3, 4))->getCurrentPage());
        self::assertSame(2, (new ListPagination(9, 3, 5))->getCurrentPage());
    }

    /**
     * Test that an out of range offset is clamped instead of producing a page that does not exist.
     *
     * @return void
     */
    public function testClampsAnOffsetBeyondTheEnd(): void
    {
        self::assertSame(3, (new ListPagination(9, 3, 999))->getCurrentPage());
        self::assertSame(1, (new ListPagination(9, 3, -5))->getCurrentPage());
    }

    /**
     * Test that all pages are offered when no window was requested.
     *
     * @return void
     */
    public function testOffersEveryPageByDefault(): void
    {
        self::assertSame([1, 2, 3, 4, 5], (new ListPagination(15, 3))->getPages());
    }

    /**
     * Test that the window slides with the current page and keeps its width.
     *
     * @param int        $offset   The offset the listing starts at.
     * @param list<int>  $expected The expected pages.
     *
     * @return void
     */
    #[DataProvider('windowProvider')]
    public function testSlidesTheWindow(int $offset, array $expected): void
    {
        self::assertSame($expected, (new ListPagination(30, 3, $offset, 3))->getPages());
    }

    /**
     * Data provider for the sliding window over ten pages with a width of three.
     *
     * @return array<string, array{int, list<int>}>
     */
    public static function windowProvider(): array
    {
        return [
            'erste Seite'    => [0, [1, 2, 3]],
            'zweite Seite'   => [3, [1, 2, 3]],
            'mittendrin'     => [12, [4, 5, 6]],
            'vorletzte'      => [24, [8, 9, 10]],
            'letzte Seite'   => [27, [8, 9, 10]],
        ];
    }

    /**
     * Test the surrounding pages.
     *
     * @return void
     */
    public function testKnowsTheSurroundingPages(): void
    {
        $first = new ListPagination(9, 3, 0);
        self::assertNull($first->getFirst());
        self::assertNull($first->getPrevious());
        self::assertSame(2, $first->getNext());
        self::assertSame(3, $first->getLast());

        $middle = new ListPagination(9, 3, 3);
        self::assertSame(1, $middle->getFirst());
        self::assertSame(1, $middle->getPrevious());
        self::assertSame(3, $middle->getNext());
        self::assertSame(3, $middle->getLast());

        $last = new ListPagination(9, 3, 6);
        self::assertSame(1, $last->getFirst());
        self::assertSame(2, $last->getPrevious());
        self::assertNull($last->getNext());
        self::assertNull($last->getLast());
    }

    /**
     * Test the offset a page starts at.
     *
     * @return void
     */
    public function testCalculatesTheOffsetForAPage(): void
    {
        $pagination = new ListPagination(9, 3);

        self::assertSame(0, $pagination->getOffsetForPage(1));
        self::assertSame(3, $pagination->getOffsetForPage(2));
        self::assertSame(6, $pagination->getOffsetForPage(3));
        self::assertSame(6, $pagination->getOffsetForPage(99), 'wird auf die letzte Seite begrenzt');
        self::assertSame(0, $pagination->getOffsetForPage(0), 'wird auf die erste Seite begrenzt');
    }

    /**
     * Test that a listing fitting on one page does not need a pagination.
     *
     * @return void
     */
    public function testIsNotNeededForASinglePage(): void
    {
        self::assertFalse((new ListPagination(0, 3))->isNeeded());
        self::assertFalse((new ListPagination(3, 3))->isNeeded());
        self::assertFalse((new ListPagination(50, 0))->isNeeded());
        self::assertTrue((new ListPagination(4, 3))->isNeeded());
    }
}
