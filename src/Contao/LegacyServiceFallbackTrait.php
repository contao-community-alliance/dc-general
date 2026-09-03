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

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Resolves the csrf token manager, csrf token name and request stack constructor arguments that
 * became optional (with a System::getContainer() fallback) when several Contao2BackendView classes
 * switched to constructor DI - triggering the same deprecation notice for callers still relying on
 * the old, shorter constructor signature.
 */
trait LegacyServiceFallbackTrait
{
    /**
     * @param CsrfTokenManagerInterface|null $tokenManager     The token manager, if given by the caller.
     * @param string                         $callingMethod    The constructor to name in the deprecation notice.
     * @param string                         $argumentPosition The ordinal position of $tokenManager in that
     *                                                          constructor's argument list, e.g. "2th".
     *
     * @return CsrfTokenManagerInterface
     */
    private static function resolveCsrfTokenManager(
        ?CsrfTokenManagerInterface $tokenManager,
        string $callingMethod,
        string $argumentPosition = '2th'
    ): CsrfTokenManagerInterface {
        if (null !== $tokenManager) {
            return $tokenManager;
        }

        $tokenManager = System::getContainer()->get('contao.csrf.token_manager');
        assert($tokenManager instanceof CsrfTokenManagerInterface);

        // phpcs:disable
        @trigger_error(
            'Not passing the csrf token manager as ' . $argumentPosition . ' argument to "' . $callingMethod .
            '" is deprecated and will cause an error in DCG 3.0',
            E_USER_DEPRECATED
        );
        // phpcs:enable

        return $tokenManager;
    }

    /**
     * @param string|null $tokenName        The token name, if given by the caller.
     * @param string      $callingMethod    The constructor to name in the deprecation notice.
     * @param string      $argumentPosition The ordinal position of $tokenName in that constructor's
     *                                      argument list, e.g. "3th".
     *
     * @return string
     */
    private static function resolveCsrfTokenName(
        ?string $tokenName,
        string $callingMethod,
        string $argumentPosition = '3th'
    ): string {
        if (null !== $tokenName) {
            return $tokenName;
        }

        $tokenName = System::getContainer()->getParameter('contao.csrf_token_name');
        assert(\is_string($tokenName));

        // phpcs:disable
        @trigger_error(
            'Not passing the csrf token name as ' . $argumentPosition . ' argument to "' . $callingMethod .
            '" is deprecated and will cause an error in DCG 3.0',
            E_USER_DEPRECATED
        );
        // phpcs:enable

        return $tokenName;
    }

    /**
     * @param RequestStack|null $requestStack     The request stack, if given by the caller.
     * @param string            $callingMethod    The constructor to name in the deprecation notice.
     * @param string            $argumentPosition The ordinal position of $requestStack in that
     *                                             constructor's argument list, e.g. "4th".
     *
     * @return RequestStack
     */
    private static function resolveRequestStack(
        ?RequestStack $requestStack,
        string $callingMethod,
        string $argumentPosition = '4th'
    ): RequestStack {
        if (null !== $requestStack) {
            return $requestStack;
        }

        $requestStack = System::getContainer()->get('request_stack');
        assert($requestStack instanceof RequestStack);

        // phpcs:disable
        @trigger_error(
            'Not passing the request stack as ' . $argumentPosition . ' argument to "' . $callingMethod .
            '" is deprecated and will cause an error in DCG 3.0',
            E_USER_DEPRECATED
        );
        // phpcs:enable

        return $requestStack;
    }
}
