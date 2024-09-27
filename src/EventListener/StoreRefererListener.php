<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\User;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Bundle\SecurityBundle\Security;

use function array_merge;
use function array_shift;
use function count;
use function is_array;
use function strlen;

/**
 * @internal
 */
class StoreRefererListener
{
    public function __construct(
        private readonly Security $security,
        private readonly ScopeMatcher $scopeMatcher
    ) {
    }

    /**
     * Stores the referer in the session.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __invoke(ResponseEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        $response = $event->getResponse();
        if (200 !== $response->getStatusCode()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        if (!$this->canModifyBackendSession($request)) {
            return;
        }

        if (!$request->hasSession()) {
            throw new RuntimeException('The request did not contain a session.');
        }

        $session   = $request->getSession();
        $key       = $request->query->has('popup') ? 'popupReferer' : 'referer';
        $refererId = $request->attributes->get('_contao_referer_id');
        $referers  = $this->prepareBackendReferer($refererId, $session->get($key));
        $ref       = (string) $request->query->get('ref', '');

        // Move current to last if the referer is in both the URL and the session.
        if ('' !== $ref && isset($referers[$ref])) {
            $referers[$refererId]         = array_merge($referers[$ref], $referers[$refererId]);
            $referers[$refererId]['last'] = $referers[$ref]['current'];
        }

        // Set new current referer
        $referers[$refererId]['current'] = $this->getRelativeRequestUri($request);

        $session->set($key, $referers);
    }

    private function canModifyBackendSession(Request $request): bool
    {
        return true === $request->attributes->get('_dcg_referer_update') && !$request->isXmlHttpRequest();
    }

    /**
     * @param string                                $refererId
     * @param ?array<string, array<string, string>> $referers
     *
     * @return array<string,array<string,string>>
     */
    private function prepareBackendReferer(string $refererId, ?array $referers = null): array
    {
        if (!is_array($referers)) {
            $referers = [];
        }

        if (!isset($referers[$refererId])) {
            $last = end($referers);
            if ([] === $last || false === $last) {
                $last = ['last' => ''];
            }
            $referers[$refererId] = $last;
        }

        // Make sure we never have more than 25 different referer URLs
        while (count($referers) >= 25) {
            array_shift($referers);
        }

        return $referers;
    }

    /**
     * Returns the current request URI relative to the base path.
     */
    private function getRelativeRequestUri(Request $request): string
    {
        return substr($request->getRequestUri(), strlen($request->getBasePath()));
    }
}
