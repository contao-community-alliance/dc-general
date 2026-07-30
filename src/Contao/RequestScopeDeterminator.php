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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The class serves as service for determining the Contao scope of the current request.
 *
 * @api
 */
class RequestScopeDeterminator
{
    /**
     * The Contao request scope matcher.
     *
     * @var ScopeMatcher
     */
    private ScopeMatcher $scopeMatcher;

    /**
     * The current request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * The request the remembered answers below belong to.
     *
     * Held weakly so that this shared service never keeps a request alive - which would matter in long running
     * workers.
     *
     * @var \WeakReference<Request>|null
     */
    private ?\WeakReference $lastRequest = null;

    /**
     * The `_scope` attribute the remembered answers were determined from, empty string when unset.
     *
     * @var string
     */
    private string $lastScope = '';

    /**
     * Whether the remembered request is a Contao request, null while undetermined.
     *
     * @var bool|null
     */
    private ?bool $isContao = null;

    /**
     * Whether the remembered request is a frontend request, null while undetermined.
     *
     * @var bool|null
     */
    private ?bool $isFrontend = null;

    /**
     * Whether the remembered request is a backend request, null while undetermined.
     *
     * @var bool|null
     */
    private ?bool $isBackend = null;

    /**
     * Create a new instance.
     *
     * @param ScopeMatcher $scopeMatcher The Contao request scope matcher.
     * @param RequestStack $requestStack The current request stack.
     */
    public function __construct(ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Check if the current scope is unknown (i.e. CLI).
     *
     * This means we have either no request or the request has neither the frontend nor backend scope set.
     *
     * @return bool
     */
    public function currentScopeIsUnknown()
    {
        if (null === ($request = $this->getCurrentRequest())) {
            return true;
        }
        $this->rememberRequest($request);

        return !($this->isContao ??= $this->scopeMatcher->isContaoRequest($request));
    }

    /**
     * Check if the current scope is frontend.
     *
     * This means we HAVE a proper request and it has the frontend scope set.
     *
     * @return bool
     */
    public function currentScopeIsFrontend()
    {
        if (null === ($request = $this->getCurrentRequest())) {
            return false;
        }
        $this->rememberRequest($request);

        return $this->isFrontend ??= $this->scopeMatcher->isFrontendRequest($request);
    }

    /**
     * Check if the current scope is backend.
     *
     * This means we have either NO request or the request has the backend scope set.
     *
     * @return bool
     */
    public function currentScopeIsBackend()
    {
        if (null === ($request = $this->getCurrentRequest())) {
            return true;
        }
        $this->rememberRequest($request);

        return $this->isBackend ??= $this->scopeMatcher->isBackendRequest($request);
    }

    /**
     * Determine the current request (if any).
     *
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Drop the remembered answers when they do not belong to the passed request any more.
     *
     * The scope of a request does not change while it is being handled, but this service is asked thousands of times
     * per request - saving a single edit mask triggered roughly 6.000 lookups. The `_scope` attribute is part of the
     * comparison because it is what the matcher bases its decision on: a request that gets its scope assigned after
     * we were first asked - which happens when a listener runs before the router - must not be served a stale answer.
     *
     * @param Request $request The request the caller is asking about.
     *
     * @return void
     */
    private function rememberRequest(Request $request): void
    {
        $scope = $request->attributes->getString('_scope');

        if ($request === $this->lastRequest?->get() && $scope === $this->lastScope) {
            return;
        }

        $this->lastRequest = \WeakReference::create($request);
        $this->lastScope   = $scope;
        $this->isContao    = null;
        $this->isFrontend  = null;
        $this->isBackend   = null;
    }
}
