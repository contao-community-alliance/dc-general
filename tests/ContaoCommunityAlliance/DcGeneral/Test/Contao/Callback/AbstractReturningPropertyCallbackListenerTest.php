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

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\ModelOptionsCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldGetWizardCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyInputFieldGetXLabelCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnLoadCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\PropertyOnSaveCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
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
        if (method_exists($class, 'getProperty')) {
            $event = $this->getMock($class, ['getEnvironment', 'getProperty'], [], '', false);
            $event
                ->expects($this->any())
                ->method('getProperty')
                ->will($this->returnValue($propertyName));
        } else {
            $event = $this->getMock($class, ['getEnvironment', 'getPropertyName'], [], '', false);
            $event
                ->expects($this->any())
                ->method('getPropertyName')
                ->will($this->returnValue($propertyName));
        }

        $event
            ->expects($this->any())
            ->method('getEnvironment')
            ->will($this->returnValue($this->mockEnvironment($tablename)));

        return $event;
    }


    public function propertyCallbackDataProvider()
    {
        return [[
                PropertyOnLoadCallbackListener::class,
                DecodePropertyValueForWidgetEvent::class
            ],
            [
                PropertyOnSaveCallbackListener::class,
                EncodePropertyValueFromWidgetEvent::class
            ],
            [
                ModelOptionsCallbackListener::class,
                GetPropertyOptionsEvent::class
            ],
            [
                PropertyInputFieldCallbackListener::class,
                BuildWidgetEvent::class
            ],
            [
                PropertyInputFieldGetWizardCallbackListener::class,
                ManipulateWidgetEvent::class
            ],
            [
                PropertyInputFieldGetXLabelCallbackListener::class,
                ManipulateWidgetEvent::class
            ],
        ];
    }

    /**
     * @dataProvider propertyCallbackDataProvider
     */
    public function testExecution($listenerClass, $eventClass)
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename', 'propertyName']);
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
