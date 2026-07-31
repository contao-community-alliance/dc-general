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

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultLimitElement;
use ContaoCommunityAlliance\DcGeneral\Panel\LimitElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\TotalAwareLimitElementInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Renders the pagination shown below a listing.
 *
 * The pages are plain links: the panel state lives in the session, so a link carries all that is
 * needed, the surrounding form is left alone and Turbo handles the navigation.
 *
 * @api
 */
class PaginationRenderer
{
    /**
     * The translation domain of the labels.
     *
     * @var string
     */
    private const string DOMAIN = 'dc-general';

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface $translator The translator.
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Render the pagination for the passed environment.
     *
     * Yields an empty string when the listing fits on a single page or when no limit element takes
     * part in the panel - there is nothing to browse then.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    public function render(EnvironmentInterface $environment): string
    {
        if (null === ($limit = $this->findLimitElement($environment))) {
            return '';
        }

        $pagination = new ListPagination($limit->getTotal(), $limit->getAmount(), $limit->getOffset());

        if (!$pagination->isNeeded()) {
            return '';
        }

        $template = new ContaoBackendViewTemplate('dcbe_general_pagination');
        $template
            ->set('pages', $this->buildPages($environment, $pagination))
            ->set('steps', $this->buildSteps($environment, $pagination))
            ->set('label', $this->translator->trans(
                'pagination.page',
                ['%current%' => $pagination->getCurrentPage(), '%total%' => $pagination->getPageCount()],
                self::DOMAIN
            ))
            ->set('labelNavigation', $this->translator->trans('pagination.navigation', [], self::DOMAIN));

        return $template->parse();
    }

    /**
     * Retrieve the limit element of the panel, if there is one that can tell the total amount.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return (LimitElementInterface&TotalAwareLimitElementInterface)|null
     */
    private function findLimitElement(EnvironmentInterface $environment)
    {
        $view = $environment->getView();
        if (!$view instanceof BackendViewInterface || (null === $panelContainer = $view->getPanel())) {
            return null;
        }

        foreach ($panelContainer as $panel) {
            foreach ($panel as $element) {
                if ($element instanceof LimitElementInterface && $element instanceof TotalAwareLimitElementInterface) {
                    return $element;
                }
            }
        }

        return null;
    }

    /**
     * Build the entry for every page to offer.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ListPagination       $pagination  The calculated pagination.
     *
     * @return list<array{page: int, url: string, current: bool}>
     */
    private function buildPages(EnvironmentInterface $environment, ListPagination $pagination): array
    {
        $pages = [];
        foreach ($pagination->getPages() as $page) {
            $pages[] = [
                'page'    => $page,
                'url'     => $this->buildUrl($environment, $page),
                'current' => $page === $pagination->getCurrentPage()
            ];
        }

        return $pages;
    }

    /**
     * Build the entries leading to the beginning, the end and the neighbouring pages.
     *
     * Absent entries are left out rather than rendered as dead ends.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ListPagination       $pagination  The calculated pagination.
     *
     * @return array<string, array{url: string, label: string}>
     */
    private function buildSteps(EnvironmentInterface $environment, ListPagination $pagination): array
    {
        $steps = [
            'first'    => $pagination->getFirst(),
            'previous' => $pagination->getPrevious(),
            'next'     => $pagination->getNext(),
            'last'     => $pagination->getLast()
        ];

        $result = [];
        foreach ($steps as $step => $page) {
            if (null === $page) {
                continue;
            }

            $result[$step] = [
                'url'   => $this->buildUrl($environment, $page),
                'label' => $this->translator->trans('pagination.' . $step, [], self::DOMAIN)
            ];
        }

        return $result;
    }

    /**
     * Build the url leading to a page.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param int                  $page        The page, one based.
     *
     * @return string
     */
    private function buildUrl(EnvironmentInterface $environment, int $page): string
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        // The request url comes without a leading slash and the back end sets no base element, so a
        // link built from it as is would be resolved against the current directory and go nowhere.
        $url = UrlBuilder::fromUrl($inputProvider->getRequestUrl())
            ->setQueryParameter(DefaultLimitElement::PAGE_PARAMETER, (string) $page)
            ->getUrl();

        return \str_starts_with($url, '/') || \preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)
            ? $url
            : '/' . $url;
    }
}
