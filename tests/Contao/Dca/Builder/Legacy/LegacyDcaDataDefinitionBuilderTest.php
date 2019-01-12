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

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\AbstractCallbackListener;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This class tests the legacy data definition builder.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::loadDca
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::process
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder::build
 * @covers \ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent::getContainer
 * @covers \ContaoCommunityAlliance\DcGeneral\DefaultEnvironment::setDataDefinition
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent::setProperty
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent::setValue
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent::getValue
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Callback\AbstractCallbackListener::wantToExecute
 */
class LegacyDcaDataDefinitionBuilderTest extends TestCase
{
    /**
     * Mocker callback for loading a dca.
     *
     * @param array           $dca
     * @param string          $eventName
     * @param Eventdispatcher $dispatcher
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|LegacyDcaDataDefinitionBuilder
     */
    public function mockBuilderWithDca($dca, $eventName, $dispatcher)
    {
        $class = LegacyDcaDataDefinitionBuilder::class;

        $mock = $this
            ->getMockBuilder($class)
            ->setMethods(['loadDca', 'process'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('loadDca')
            ->will($this->returnCallback(
                function () use ($mock, $dca, $class) {
                    $reflection = new \ReflectionProperty($class, 'dca');
                    $reflection->setAccessible(true);
                    $reflection->setValue($mock, $dca);

                    return true;
                }
            ));

        $reflection = new \ReflectionProperty($class, 'eventName');
        $reflection->setAccessible(true);
        $reflection->setValue($mock, $eventName);

        $reflection = new \ReflectionProperty($class, 'dispatcher');
        $reflection->setAccessible(true);
        $reflection->setValue($mock, $dispatcher);

        return $mock;
    }

    /**
     * Check that the parsing of the callbacks is working.
     *
     * @return void
     */
    public function testCallbackParsing()
    {
        $this->aliasContaoClass('Session');
        $this->aliasContaoClass('System');
        $this->aliasContaoClass('Controller');
        $this->aliasContaoClass('Backend');
        $this->aliasContaoClass('DataContainer');

        $dispatcher = new EventDispatcher();
        $container  = new DefaultContainer('tl_test');
        $event      = new BuildDataDefinitionEvent($container);
        $builder    = $this->mockBuilderWithDca([
                'fields' => [
                    'testProperty' => [
                        'save_callback' => [
                            function () {
                                return 'executed';
                            }
                        ]
                    ]
                ]
            ],
            $event::NAME,
            $dispatcher
        );

        $builder->build($event->getContainer(), $event);
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition($container);

        $event = new EncodePropertyValueFromWidgetEvent(
            $environment,
            new DefaultModel(),
            new PropertyValueBag()
        );

        $event->setProperty('testProperty');
        $this->assertCount(1, $dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME));
        foreach ($dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME) as $listener) {
            /** @var AbstractCallbackListener $listener */
            $this->assertTrue($listener->wantToExecute($event));
            $event->setValue('testvalue');
            $listener($event);
            $this->assertEquals('executed', $event->getValue());
        }

        $event->setProperty('testProperty2');

        foreach ($dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME) as $listener) {
            /** @var AbstractCallbackListener $listener */
            $this->assertFalse($listener->wantToExecute($event));
        }
    }
}
