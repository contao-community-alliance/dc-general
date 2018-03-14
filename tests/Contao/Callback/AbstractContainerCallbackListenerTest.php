<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
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
            $event = $this->getMock($class, ['getEnvironment'], [], '', false);
            if ($tablename) {
                $event
                    ->expects($this->any())
                    ->method('getEnvironment')
                    ->will($this->returnValue($this->mockEnvironment($tablename)));
            }
        } else {
            $event = $this->getMock($class, ['unknownMethod'], [], '', true);
        }

        return $event;
    }


    public function testEnvironmentAwareEventExecutionDataProvider()
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
     * @dataProvider testEnvironmentAwareEventExecutionDataProvider
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

    public function testEnvironmentUnawareEventExecutionDataProvider()
    {
        $that = $this;
        return [[
                ContainerOnLoadCallbackListener::class,
                function ($tableName) use ($that) {
                    $event = $that->getMock(
                        CreateDcGeneralEvent::class,
                        ['getDcGeneral'],
                        [],
                        '',
                        false
                    );
                    if ($tableName) {
                        $event
                            ->expects($that->any())
                            ->method('getDcGeneral')
                            ->will($that->returnValue(new DcGeneral($that->mockEnvironment($tableName))));
                    }

                    return $event;
                }
            ],
        ];
    }

    /**
     * @dataProvider testEnvironmentUnawareEventExecutionDataProvider
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

    public function testOperationRestrictedEventExecutionDataProvider()
    {
        $that = $this;
        return [[
                ContainerGlobalButtonCallbackListener::class,
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        GetGlobalButtonEvent::class,
                        ['getEnvironment', 'getKey'],
                        [],
                        '',
                        false
                    );

                    $event
                        ->expects($that->any())
                        ->method('getEnvironment')
                        ->will($that->returnValue($that->mockEnvironment($tableName)));
                    $event
                        ->expects($that->any())
                        ->method('getKey')
                        ->will($that->returnValue($operationName));

                    return $event;
                }
            ],
            [
                ModelOperationButtonCallbackListener::class,
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        GetOperationButtonEvent::class,
                        ['getEnvironment', 'getKey'],
                        [],
                        '',
                        false
                    );

                    $event
                        ->expects($that->any())
                        ->method('getEnvironment')
                        ->will($that->returnValue($that->mockEnvironment($tableName)));
                    $event
                        ->expects($that->any())
                        ->method('getKey')
                        ->will($that->returnValue($operationName));

                    return $event;
                }
            ],
        ];
    }

    /**
     * @dataProvider testOperationRestrictedEventExecutionDataProvider
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
