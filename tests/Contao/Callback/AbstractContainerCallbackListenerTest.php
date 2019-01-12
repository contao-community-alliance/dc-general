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

/**
 * Class AbstractContainerCallbackListenerTest
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent::getDcGeneral
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent::getKey
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener::wantToExecute
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent::getEnvironment
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent::getKey
 */
class AbstractContainerCallbackListenerTest extends TestCase
{
    protected function getCallback($value)
    {
        return function () use($value) {
            throw new \Exception('The callback should not be executed as it is only mocked');
        };
    }

    public function mockEnvironment($dataContainerName)
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockContainerEvent($class, $tablename)
    {
        $reflection = new \ReflectionClass($class);

        if ($reflection->hasMethod('getEnvironment')) {
                $event = $this
                    ->getMockBuilder($class)
                    ->setMethods(['getEnvironment'])
                    ->disableOriginalConstructor()
                    ->getMock();

            if ($tablename) {
                $event
                    ->method('getEnvironment')
                    ->willReturn($this->mockEnvironment($tablename));
            }
        } else {

            $event = $this
                ->getMockBuilder($class)
                ->setMethods(['unknownMethod'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $event;
    }


    public function environmentAwareEventExecutionDataProvider()
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

    /**
     * @dataProvider environmentAwareEventExecutionDataProvider
     */
    public function testEnvironmentAwareEventExecution($listenerClass, $eventClass)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename']);
        $this->assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'tablename')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'anotherTable')),
            $listenerClass
        );

        $listener = new $listenerClass($this->getCallback($listenerClass));
        $this->assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'tablename')),
            $listenerClass
        );
        $this->assertTrue(
            $listener->wantToExecute($this->mockContainerEvent($eventClass, 'anotherTable')),
            $listenerClass
        );
    }

    public function environmentUnawareEventExecutionDataProvider()
    {
        $that = $this;
        return [[
                ContainerOnLoadCallbackListener::class,
                function ($tableName) use ($that) {
                    $event = $this
                        ->getMockBuilder(CreateDcGeneralEvent::class)
                        ->setMethods(['getDcGeneral'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    if ($tableName) {
                        $event
                            ->method('getDcGeneral')
                            ->willReturn(new DcGeneral($that->mockEnvironment($tableName)));
                    }
                    return $event;
                }
            ],
        ];
    }

    /**
     * @dataProvider environmentUnawareEventExecutionDataProvider
     */
    public function testEnvironmentUnawareEventExecution($listenerClass, $eventFactory)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename']);
        $this->assertTrue($listener->wantToExecute($eventFactory('tablename')), $listenerClass);
        $this->assertFalse($listener->wantToExecute($eventFactory('anotherTable')), $listenerClass);

        $listener = new $listenerClass($this->getCallback($listenerClass));
        $this->assertTrue($listener->wantToExecute($eventFactory('tablename')), $listenerClass);
        $this->assertTrue($listener->wantToExecute($eventFactory('anotherTable')), $listenerClass);
    }

    public function operationRestrictedEventExecutionDataProvider()
    {
        $that = $this;
        return [[
                ContainerGlobalButtonCallbackListener::class,
                function ($tableName, $operationName) use ($that) {
                    $event = $this
                        ->getMockBuilder(GetGlobalButtonEvent::class)
                        ->setMethods(['getEnvironment', 'getKey'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    $event
                        ->method('getEnvironment')
                        ->willReturn($that->mockEnvironment($tableName));
                    $event
                        ->method('getKey')
                        ->willReturn($operationName);

                    return $event;
                }
            ],
            [
                ModelOperationButtonCallbackListener::class,
                function ($tableName, $operationName) use ($that) {
                    $event = $this
                        ->getMockBuilder(GetOperationButtonEvent::class)
                        ->setMethods(['getEnvironment', 'getKey'])
                        ->disableOriginalConstructor()
                        ->getMock();

                    $event
                        ->method('getEnvironment')
                        ->willReturn($that->mockEnvironment($tableName));
                    $event
                        ->method('getKey')
                        ->willReturn($operationName);

                    return $event;
                }
            ],
        ];
    }

    /**
     * @dataProvider operationRestrictedEventExecutionDataProvider
     */
    public function testOperationRestrictedEventExecution($listenerClass, $eventFactory)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename', 'operationName']);
        $this->assertTrue(
            $listener->wantToExecute($eventFactory('tablename', 'operationName')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($eventFactory('anotherTable', 'operationName')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($eventFactory('tablename', 'anotherOperationName')),
            $listenerClass
        );
    }
}
