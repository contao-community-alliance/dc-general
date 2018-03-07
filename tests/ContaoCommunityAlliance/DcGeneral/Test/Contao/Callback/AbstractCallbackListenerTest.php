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

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\AbstractCallbackListener;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class AbstractCallbackListenerTest extends TestCase
{
    protected function getCallback($value)
    {
        return function () use ($value) {
            throw new \Exception('The callback should not be executed as it is only mocked');
        };
    }

    public function abstractCallbackDataProvider()
    {
        return [[
                AbstractCallbackListener::class,
                AbstractEnvironmentAwareEvent::class
            ],
        ];
    }

    protected function mockEnvironment($dataContainerName)
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockEnvironmentEvent($class, $tablename)
    {
        $event = $this->getMock($class, ['getEnvironment'], [], '', false);

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
        $listener = $this->getMock($listenerClass, ['getArgs'], [$this->getCallback($listenerClass)]);

        $this->assertTrue(
            $listener->wantToExecute($this->mockEnvironmentEvent($eventClass, 'tablename')),
            $listenerClass
        );

        $listener = $this->getMock($listenerClass, ['getArgs'], [$this->getCallback($listenerClass), ['tablename']]);

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
