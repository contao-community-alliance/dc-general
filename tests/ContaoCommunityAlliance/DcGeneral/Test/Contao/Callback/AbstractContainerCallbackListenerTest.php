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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
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
        return [
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent'
            ],
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent'
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
        return [
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener',
                function ($tableName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent',
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
        $this->assertTrue(
            $listener->wantToExecute($eventFactory('tablename')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($eventFactory('anotherTable')),
            $listenerClass
        );

        $listener = new $listenerClass($this->getCallback($listenerClass));
        $this->assertTrue(
            $listener->wantToExecute($eventFactory('tablename')),
            $listenerClass
        );
        $this->assertTrue(
            $listener->wantToExecute($eventFactory('anotherTable')),
            $listenerClass
        );
    }

    public function testOperationRestrictedEventExecutionDataProvider()
    {
        $that = $this;
        return [
            [
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener',
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent',
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
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener',
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent',
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
