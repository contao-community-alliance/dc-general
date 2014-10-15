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

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnLoadCallbackListener;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class AbstractReturningPropertyCallbackListenerTest extends TestCase
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

    protected function mockPropertyEvent($class, $tablename, $propertyName)
    {
        $event = $this->getMock(
            $class,
            array('getEnvironment', 'getProperty'),
            array(),
            '',
            false
        );

        $event
            ->expects($this->any())
            ->method('getEnvironment')
            ->will($this->returnValue($this->mockEnvironment($tablename)));

        $event
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue($propertyName));

        return $event;
    }


    public function propertyCallbackDataProvider()
    {
        return array(
        );
    }

    /**
     * @dataProvider propertyCallbackDataProvider
     */
    public function testExecution($listenerClass, $eventClass)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), array('tablename', 'propertyName'));
        $this->assertTrue(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'tablename', 'propertyName')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'anotherTable', 'propertyName')),
            $listenerClass
        );
        $this->assertFalse(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'tablename', 'anotherPropertyName')),
            $listenerClass
        );
    }
}
