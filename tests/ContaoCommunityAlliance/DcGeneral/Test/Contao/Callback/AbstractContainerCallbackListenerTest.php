<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * Class AbstractContainerCallbackListenerTest
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\Contao\Callback
 */
class AbstractContainerCallbackListenerTest extends TestCase
{
    protected function getCallback($value)
    {
        return function () use($value) {
            throw new \Exception('The callback should not be executed as it is only mocked');
        };
    }

    protected function mockEnvironment($dataContainerName)
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockContainerEvent($class, $tablename)
    {
        $reflection = new \ReflectionClass($class);

        if ($reflection->hasMethod('getEnvironment')) {
            $event = $this->getMock(
                $class,
                array('getEnvironment'),
                array(),
                '',
                false
            );
            if ($tablename) {
                $event
                    ->expects($this->any())
                    ->method('getEnvironment')
                    ->will($this->returnValue($this->mockEnvironment($tablename)));
            }
        } else {
            $event = $this->getMock(
                $class,
                array('unknownMethod'),
                array(),
                '',
                true
            );
        }

        return $event;
    }


    public function testEnvironmentAwareEventExecutionDataProvider()
    {
        return array(
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnSubmitCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnDeleteCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCutCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnCopyCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerHeaderCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteRootButtonCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerPasteButtonCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelChildRecordCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelGroupCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelLabelCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent'
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGetBreadcrumbCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent'
            ),
        );
    }

    /**
     * @dataProvider testEnvironmentAwareEventExecutionDataProvider
     */
    public function testEnvironmentAwareEventExecution($listenerClass, $eventClass)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), array('tablename'));
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
        return array(
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerOnLoadCallbackListener',
                function ($tableName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Factory\Event\CreateDcGeneralEvent',
                        array('getDcGeneral'),
                        array(),
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
            ),
        );
    }

    /**
     * @dataProvider testEnvironmentUnawareEventExecutionDataProvider
     */
    public function testEnvironmentUnawareEventExecution($listenerClass, $eventFactory)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), array('tablename'));
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
        return array(
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ContainerGlobalButtonCallbackListener',
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent',
                        array('getEnvironment', 'getKey'),
                        array(),
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
            ),
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOperationButtonCallbackListener',
                function ($tableName, $operationName) use ($that) {
                    $event = $that->getMock(
                        'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent',
                        array('getEnvironment', 'getKey'),
                        array(),
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
            ),
        );
    }

    /**
     * @dataProvider testOperationRestrictedEventExecutionDataProvider
     */
    public function testOperationRestrictedEventExecution($listenerClass, $eventFactory)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), array('tablename', 'operationName'));
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
