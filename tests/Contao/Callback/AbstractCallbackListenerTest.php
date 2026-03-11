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

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Callback;

use Closure;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\AbstractCallbackListener;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(AbstractCallbackListener::class, 'wantToExecute')]
#[CoversMethod(AbstractCallbackListener::class, 'getArgs')]
#[CoversMethod(AbstractEnvironmentAwareEvent::class, 'getEnvironment')]
final class AbstractCallbackListenerTest extends TestCase
{
    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) - phpmd cannot handle the use syntax. */
    protected function getCallback(string $value): Closure
    {
        return static function () use ($value) {
            throw new RuntimeException('The callback should not be executed as it is only mocked: ' . $value);
        };
    }

    public static function abstractCallbackDataProvider(): array
    {
        return [[
                AbstractCallbackListener::class,
                AbstractEnvironmentAwareEvent::class
            ],
        ];
    }

    protected function mockEnvironment(string $dataContainerName): DefaultEnvironment
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    /** @param class-string<AbstractEnvironmentAwareEvent> $class */
    protected function mockEnvironmentEvent(string $class, string $tablename): AbstractEnvironmentAwareEvent
    {
        /** @var MockObject&AbstractEnvironmentAwareEvent $event */
        $event = $this
            ->getMockBuilder($class)
            ->onlyMethods(['getEnvironment'])
            ->setConstructorArgs([$this->mockEnvironment($tablename)])
            ->getMock();

        $event
            ->method('getEnvironment')
            ->willReturn($this->mockEnvironment($tablename));

        return $event;
    }

    #[Dataprovider('abstractCallbackDataProvider')]
    public function testExecution(string $listenerClass, string $eventClass): void
    {
        /** @var AbstractCallbackListener $listener */
        $listener = $this
            ->getMockBuilder($listenerClass)
            ->onlyMethods(['getArgs'])
            ->setConstructorArgs([$this->getCallback($listenerClass)])
            ->getMock();

        self::assertTrue(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'tablename')),
            $listenerClass
        );

        /** @var AbstractCallbackListener $listener */
        $listener = $this
            ->getMockBuilder($listenerClass)
            ->onlyMethods(['getArgs'])
            ->setConstructorArgs([$this->getCallback($listenerClass), ['tablename']])
            ->getMock();

        self::assertTrue(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'tablename')),
            $listenerClass
        );

        self::assertFalse(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'anotherTable')),
            $listenerClass
        );
    }
}
