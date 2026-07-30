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

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao;

use Contao\CoreBundle\Routing\ScopeMatcher;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This tests the request scope determinator.
 */
#[CoversClass(RequestScopeDeterminator::class)]
final class RequestScopeDeterminatorTest extends TestCase
{
    /**
     * Test that the answer is obtained from the matcher and passed through.
     *
     * @return void
     */
    public function testDeterminesTheScope(): void
    {
        $request = $this->mockRequest('backend');
        $matcher = $this->mockMatcher(backend: true, frontend: false, contao: true);

        $determinator = new RequestScopeDeterminator($matcher, $this->mockStack($request));

        self::assertTrue($determinator->currentScopeIsBackend());
        self::assertFalse($determinator->currentScopeIsFrontend());
        self::assertFalse($determinator->currentScopeIsUnknown());
    }

    /**
     * Test that the matcher is consulted only once per request and answer.
     *
     * @return void
     */
    public function testAsksTheMatcherOnlyOnce(): void
    {
        $request = $this->mockRequest('backend');

        $matcher = $this->getMockBuilder(ScopeMatcher::class)->disableOriginalConstructor()->getMock();
        $matcher->expects(self::once())->method('isBackendRequest')->with($request)->willReturn(true);

        $determinator = new RequestScopeDeterminator($matcher, $this->mockStack($request));

        for ($i = 0; $i < 100; $i++) {
            self::assertTrue($determinator->currentScopeIsBackend());
        }
    }

    /**
     * Test that a different request is determined anew instead of reusing the previous answer.
     *
     * @return void
     */
    public function testDeterminesEachRequestOnItsOwn(): void
    {
        $backendRequest  = $this->mockRequest('backend');
        $frontendRequest = $this->mockRequest('frontend');

        $stack = $this->createStub(RequestStack::class);
        $stack->method('getCurrentRequest')->willReturnOnConsecutiveCalls(
            $backendRequest,
            $frontendRequest,
            $backendRequest
        );

        $matcher = $this->createStub(ScopeMatcher::class);
        $matcher->method('isBackendRequest')->willReturnCallback(
            static fn (Request $request): bool => 'backend' === $request->attributes->get('_scope')
        );

        $determinator = new RequestScopeDeterminator($matcher, $stack);

        self::assertTrue($determinator->currentScopeIsBackend());
        self::assertFalse($determinator->currentScopeIsBackend());
        self::assertTrue($determinator->currentScopeIsBackend());
    }

    /**
     * Test that a scope assigned after the first question invalidates the remembered answer.
     *
     * This is what happens when something asks before the router has run.
     *
     * @return void
     */
    public function testForgetsTheAnswerWhenTheScopeIsAssignedLater(): void
    {
        $request = $this->mockRequest(null);

        $matcher = $this->createStub(ScopeMatcher::class);
        $matcher->method('isBackendRequest')->willReturnCallback(
            static fn (Request $request): bool => 'backend' === $request->attributes->get('_scope')
        );

        $determinator = new RequestScopeDeterminator($matcher, $this->mockStack($request));

        self::assertFalse($determinator->currentScopeIsBackend());

        $request->attributes->set('_scope', 'backend');

        self::assertTrue($determinator->currentScopeIsBackend());
    }

    /**
     * Test that the answers without a request stay as they were.
     *
     * @return void
     */
    public function testAnswersWithoutRequest(): void
    {
        $stack = $this->createStub(RequestStack::class);
        $stack->method('getCurrentRequest')->willReturn(null);

        $matcher = $this->getMockBuilder(ScopeMatcher::class)->disableOriginalConstructor()->getMock();
        $matcher->expects(self::never())->method('isBackendRequest');

        $determinator = new RequestScopeDeterminator($matcher, $stack);

        self::assertTrue($determinator->currentScopeIsUnknown());
        self::assertTrue($determinator->currentScopeIsBackend());
        self::assertFalse($determinator->currentScopeIsFrontend());
    }

    /**
     * Build a request carrying the passed scope.
     *
     * @param string|null $scope The scope to set, null to leave it unset.
     *
     * @return Request
     */
    private function mockRequest(?string $scope): Request
    {
        $request = new Request();
        if (null !== $scope) {
            $request->attributes->set('_scope', $scope);
        }

        return $request;
    }

    /**
     * Build a request stack always returning the passed request.
     *
     * @param Request $request The request to return.
     *
     * @return RequestStack&\PHPUnit\Framework\MockObject\Stub
     */
    private function mockStack(Request $request): RequestStack
    {
        $stack = $this->createStub(RequestStack::class);
        $stack->method('getCurrentRequest')->willReturn($request);

        return $stack;
    }

    /**
     * Build a scope matcher answering as passed.
     *
     * @param bool $backend  The answer for backend requests.
     * @param bool $frontend The answer for frontend requests.
     * @param bool $contao   The answer for Contao requests.
     *
     * @return ScopeMatcher&\PHPUnit\Framework\MockObject\Stub
     */
    private function mockMatcher(bool $backend, bool $frontend, bool $contao): ScopeMatcher
    {
        $matcher = $this->createStub(ScopeMatcher::class);
        $matcher->method('isBackendRequest')->willReturn($backend);
        $matcher->method('isFrontendRequest')->willReturn($frontend);
        $matcher->method('isContaoRequest')->willReturn($contao);

        return $matcher;
    }
}
