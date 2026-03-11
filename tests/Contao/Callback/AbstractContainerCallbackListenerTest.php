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
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AbstractContainerCallbackListenerTest
 *
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(ContainerOnSubmitCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PostPersistModelEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerOnDeleteCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PostDeleteModelEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerOnCutCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PostPasteModelEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerOnCopyCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PostDuplicateModelEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerHeaderCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetParentHeaderEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerPasteRootButtonCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetPasteRootButtonEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerPasteButtonCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetPasteButtonEvent::class, 'getEnvironment')]
#[CoversMethod(ModelChildRecordCallbackListener::class, 'wantToExecute')]
#[CoversMethod(ParentViewChildRecordEvent::class, 'getEnvironment')]
#[CoversMethod(ModelGroupCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetGroupHeaderEvent::class, 'getEnvironment')]
#[CoversMethod(ModelLabelCallbackListener::class, 'wantToExecute')]
#[CoversMethod(ModelToLabelEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerGetBreadcrumbCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetBreadcrumbEvent::class, 'getEnvironment')]
#[CoversMethod(ContainerOnLoadCallbackListener::class, 'wantToExecute')]
#[CoversMethod(CreateDcGeneralEvent::class, 'getDcGeneral')]
#[CoversMethod(ContainerGlobalButtonCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetGlobalButtonEvent::class, 'getEnvironment')]
#[CoversMethod(GetGlobalButtonEvent::class, 'getKey')]
#[CoversMethod(ModelOperationButtonCallbackListener::class, 'wantToExecute')]
#[CoversMethod(GetOperationButtonEvent::class, 'getEnvironment')]
#[CoversMethod(GetOperationButtonEvent::class, 'getKey')]
final class AbstractContainerCallbackListenerTest extends TestCase
{
    protected function getCallback($value): Closure
    {
        return static function () use ($value) {
            throw new RuntimeException('The callback should not be executed as it is only mocked: ' . $value);
        };
    }

    public static function getEnvironment($dataContainerName): DefaultEnvironment
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockContainerEvent(string $class, string $tablename): Event
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->hasMethod('getEnvironment')) {
            /** @var Event&MockObject $event */
            $event = $this
                ->getMockBuilder($class)
                ->onlyMethods(['getEnvironment'])
                ->disableOriginalConstructor()
                ->getMock();

            if ($tablename) {
                $event
                    ->method('getEnvironment')
                    ->willReturn(self::getEnvironment($tablename));
            }
        } else {
            /** @var Event&MockObject $event */
            $event = $this
                ->getMockBuilder($class)
                ->onlyMethods(['unknownMethod'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $event;
    }

    public static function environmentAwareEventExecutionDataProvider(): array
    {
        return [[
                ContainerOnSubmitCallbackListener::class,
                PostPersistModelEvent::class
            ],
            [
                ContainerOnDeleteCallbackListener::class,
                PostDeleteModelEvent::class
            ],
            [
                ContainerOnCutCallbackListener::class,
                PostPasteModelEvent::class
            ],
            [
                ContainerOnCopyCallbackListener::class,
                PostDuplicateModelEvent::class
            ],
            [
                ContainerHeaderCallbackListener::class,
                GetParentHeaderEvent::class
            ],
            [
                ContainerPasteRootButtonCallbackListener::class,
                GetPasteRootButtonEvent::class
            ],
            [
                ContainerPasteButtonCallbackListener::class,
                GetPasteButtonEvent::class
            ],
            [
                ModelChildRecordCallbackListener::class,
                ParentViewChildRecordEvent::class
            ],
            [
                ModelGroupCallbackListener::class,
                GetGroupHeaderEvent::class
            ],
            [
                ModelLabelCallbackListener::class,
                ModelToLabelEvent::class
            ],
            [
                ContainerGetBreadcrumbCallbackListener::class,
                GetBreadcrumbEvent::class
            ],
        ];
    }

    #[Dataprovider('environmentAwareEventExecutionDataProvider')]
    public function testEnvironmentAwareEventExecution(string $listenerClass, string $eventClass): void
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename']);
        self::assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'tablename')),
            $listenerClass
        );
        self::assertFalse(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'anotherTable')),
            $listenerClass
        );

        $listener = new $listenerClass($this->getCallback($listenerClass));
        self::assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'tablename')),
            $listenerClass
        );
        self::assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'anotherTable')),
            $listenerClass
        );
    }

    public static function environmentUnawareEventExecutionDataProvider(): array
    {
        return [
            [
                ContainerOnLoadCallbackListener::class,
                static function ($tableName, TestCase $test) {
                    $event = $test
                        ->getMockBuilder(CreateDcGeneralEvent::class)
                        ->onlyMethods(['getDcGeneral'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    if ($tableName) {
                        $event
                            ->method('getDcGeneral')
                            ->willReturn(new DcGeneral(self::getEnvironment($tableName)));
                    }
                    return $event;
                }
            ],
        ];
    }

    #[Dataprovider('environmentUnawareEventExecutionDataProvider')]
    public function testEnvironmentUnawareEventExecution($listenerClass, $eventFactory): void
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename']);
        self::assertTrue($listener->wantToExecute($eventFactory('tablename', $this)), $listenerClass);
        self::assertFalse($listener->wantToExecute($eventFactory('anotherTable', $this)), $listenerClass);

        $listener = new $listenerClass($this->getCallback($listenerClass));
        self::assertTrue($listener->wantToExecute($eventFactory('tablename', $this)), $listenerClass);
        self::assertTrue($listener->wantToExecute($eventFactory('anotherTable', $this)), $listenerClass);
    }

    public static function operationRestrictedEventExecutionDataProvider(): array
    {
        return [
            [
                ContainerGlobalButtonCallbackListener::class,
                static function ($tableName, $operationName, TestCase $test) {
                    $event = $test
                        ->getMockBuilder(GetGlobalButtonEvent::class)
                        ->onlyMethods(['getEnvironment', 'getKey'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    $event
                        ->method('getEnvironment')
                        ->willReturn(self::getEnvironment($tableName));
                    $event
                        ->method('getKey')
                        ->willReturn($operationName);

                    return $event;
                }
            ],
            [
                ModelOperationButtonCallbackListener::class,
                function ($tableName, $operationName, TestCase $test) {
                    $event = $test
                        ->getMockBuilder(GetOperationButtonEvent::class)
                        ->onlyMethods(['getEnvironment', 'getKey'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    $event
                        ->method('getEnvironment')
                        ->willReturn(self::getEnvironment($tableName));
                    $event
                        ->method('getKey')
                        ->willReturn($operationName);

                    return $event;
                }
            ],
        ];
    }

    #[Dataprovider('operationRestrictedEventExecutionDataProvider')]
    public function testOperationRestrictedEventExecution($listenerClass, $eventFactory): void
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename', 'operationName']);
        self::assertTrue(
            $listener->wantToExecute($eventFactory('tablename', 'operationName', $this)),
            $listenerClass
        );
        self::assertFalse(
            $listener->wantToExecute($eventFactory('anotherTable', 'operationName', $this)),
            $listenerClass
        );
        self::assertFalse(
            $listener->wantToExecute($eventFactory('tablename', 'anotherOperationName', $this)),
            $listenerClass
        );
    }
}
