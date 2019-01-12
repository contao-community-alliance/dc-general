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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The class serves as service for determining the Contao scope of the current request.
 */
class RequestScopeDeterminator
{
    /**
     * The Contao request scope matcher.
     *
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * The current request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

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
        return (null === ($request = $this->getCurrentRequest())) || !$this->scopeMatcher->isContaoRequest($request);
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
        return (null !== ($request = $this->getCurrentRequest())) && $this->scopeMatcher->isFrontendRequest($request);
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
        return (null === ($request = $this->getCurrentRequest())) || $this->scopeMatcher->isBackendRequest($request);
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
}
