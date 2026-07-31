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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

/**
 * Calculates which pages a listing has and which of them to offer.
 *
 * Plain arithmetic without any dependency, so that it can be reasoned about and tested on its own.
 * The window of offered pages slides with the current page and is clamped at both ends; there are
 * no ellipses. By default every page is offered, which is what a back end listing wants.
 *
 * @api
 */
final class ListPagination
{
    /**
     * The total amount of pages, at least one.
     *
     * @var int
     */
    private int $pageCount;

    /**
     * The page the listing currently shows, one based.
     *
     * @var int
     */
    private int $currentPage;

    /**
     * The pages to offer, one based and ascending.
     *
     * @var list<int>
     */
    private array $pages;

    /**
     * Create a new instance.
     *
     * An offset that does not sit on a page boundary - which happens after the amount per page has
     * been changed - is rounded down to the page containing it rather than being rejected.
     *
     * @param int      $total     The total amount of records matching the current filter.
     * @param int      $perPage   The amount of records shown per page, zero or less meaning "all".
     * @param int      $offset    The offset the listing currently starts at.
     * @param int|null $pageRange The amount of pages to offer, null meaning "all of them".
     */
    public function __construct(
        private readonly int $total,
        private readonly int $perPage,
        int $offset = 0,
        ?int $pageRange = null
    ) {
        if ($this->perPage < 1) {
            $this->pageCount   = 1;
            $this->currentPage = 1;
            $this->pages       = [1];

            return;
        }

        $this->pageCount   = \max(1, (int) \ceil($this->total / $this->perPage));
        $this->currentPage = \min($this->pageCount, \max(1, (int) \floor(\max(0, $offset) / $this->perPage) + 1));

        $this->pages = $this->calculatePages(
            (null === $pageRange || $pageRange > $this->pageCount || $pageRange < 1)
                ? $this->pageCount
                : $pageRange
        );
    }

    /**
     * Determine whether the listing spans more than a single page.
     *
     * A pagination showing exactly one page is noise - the caller can skip rendering it.
     *
     * @return bool
     */
    public function isNeeded(): bool
    {
        return $this->pageCount > 1;
    }

    /**
     * Retrieve the total amount of records.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Retrieve the amount of records per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Retrieve the total amount of pages, at least one.
     *
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * Retrieve the page the listing currently shows, one based.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Retrieve the pages to offer, one based and ascending.
     *
     * @return list<int>
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * Retrieve the first page, or null when the listing already shows it.
     *
     * @return int|null
     */
    public function getFirst(): ?int
    {
        return $this->currentPage > 1 ? 1 : null;
    }

    /**
     * Retrieve the page before the current one, or null when there is none.
     *
     * @return int|null
     */
    public function getPrevious(): ?int
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : null;
    }

    /**
     * Retrieve the page after the current one, or null when there is none.
     *
     * @return int|null
     */
    public function getNext(): ?int
    {
        return $this->currentPage < $this->pageCount ? $this->currentPage + 1 : null;
    }

    /**
     * Retrieve the last page, or null when the listing already shows it.
     *
     * @return int|null
     */
    public function getLast(): ?int
    {
        return $this->currentPage < $this->pageCount ? $this->pageCount : null;
    }

    /**
     * Determine the offset a page starts at.
     *
     * @param int $page The page, one based.
     *
     * @return int
     */
    public function getOffsetForPage(int $page): int
    {
        if ($this->perPage < 1) {
            return 0;
        }

        return (\min($this->pageCount, \max(1, $page)) - 1) * $this->perPage;
    }

    /**
     * Determine the window of pages to offer.
     *
     * The window is centred on the current page and pushed inwards once it would leave the range,
     * so that its width stays the same on every page.
     *
     * @param int $pageRange The width of the window, already clamped to the amount of pages.
     *
     * @return list<int>
     */
    private function calculatePages(int $pageRange): array
    {
        $delta = (int) \ceil($pageRange / 2);

        if ($this->currentPage - $delta > $this->pageCount - $pageRange) {
            return \range($this->pageCount - $pageRange + 1, $this->pageCount);
        }

        if ($this->currentPage - $delta < 0) {
            $delta = $this->currentPage;
        }

        $offset = $this->currentPage - $delta;

        return \range($offset + 1, $offset + $pageRange);
    }
}
