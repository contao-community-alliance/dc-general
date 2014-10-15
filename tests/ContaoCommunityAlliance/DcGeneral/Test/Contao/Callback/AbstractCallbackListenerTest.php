<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class AbstractCallbackListenerTest extends TestCase
{
    protected function getCallback($value)
    {
        return function () use($value) {
            throw new \Exception('The callback should not be executed as it is only mocked');
        };
    }

    public function abstractCallbackDataProvider()
    {
        return array(
            array(
                'ContaoCommunityAlliance\DcGeneral\Contao\Callback\AbstractCallbackListener',
                'ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent'
            ),
        );
    }

    protected function mockEnvironment($dataContainerName)
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockEnvironmentEvent($class, $tablename)
    {
        $event = $this->getMock(
            $class,
            array('getEnvironment'),
            array(),
            '',
            false
        );

        $event
            ->expects($this->any())
            ->method('getEnvironment')
            ->will($this->returnValue($this->mockEnvironment($tablename)));

        return $event;
    }

    /**
     * @dataProvider abstractCallbackDataProvider
     */
    public function testExecution($listenerClass, $eventClass)
    {
        $listener = $this->getMock($listenerClass, array('getArgs'), array($this->getCallback($listenerClass)));

        $this->assertTrue(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'tablename')),
            $listenerClass
        );

        $listener = $this->getMock($listenerClass, array('getArgs'), array($this->getCallback($listenerClass), array('tablename')));

        $this->assertTrue(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'tablename')),
            $listenerClass
        );

        $this->assertFalse(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'anotherTable')),
            $listenerClass
        );
    }
}
