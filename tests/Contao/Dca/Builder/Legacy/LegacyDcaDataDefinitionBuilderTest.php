<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
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
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This class tests the legacy data definition builder.
 */
#[CoversMethod(LegacyDcaDataDefinitionBuilder::class, 'loadDca')]
#[CoversMethod(LegacyDcaDataDefinitionBuilder::class, 'process')]
#[CoversMethod(LegacyDcaDataDefinitionBuilder::class, 'build')]
#[CoversMethod(BuildDataDefinitionEvent::class, 'getContainer')]
#[CoversMethod(DefaultEnvironment::class, 'setDataDefinition')]
#[CoversMethod(EncodePropertyValueFromWidgetEvent::class, 'setProperty')]
#[CoversMethod(EncodePropertyValueFromWidgetEvent::class, 'setValue')]
#[CoversMethod(EncodePropertyValueFromWidgetEvent::class, 'getValue')]
#[CoversMethod(AbstractCallbackListener::class, 'wantToExecute')]
final class LegacyDcaDataDefinitionBuilderTest extends TestCase
{
    /**
     * Mocker callback for loading a dca.
     *
     * @param array           $dca
     * @param string          $eventName
     * @param Eventdispatcher $dispatcher
     *
     * @return MockObject|LegacyDcaDataDefinitionBuilder
     */
    public function mockBuilderWithDca(
        array $dca,
        string $eventName,
        EventDispatcher $dispatcher
    ): MockObject&LegacyDcaDataDefinitionBuilder {
        $class = LegacyDcaDataDefinitionBuilder::class;

        $mock = $this
            ->getMockBuilder($class)
            ->onlyMethods(['loadDca', 'process'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('loadDca')
            ->willReturnCallback(
                function () use ($mock, $dca, $class) {
                    $reflection = new ReflectionProperty($class, 'dca');
                    $reflection->setValue($mock, $dca);

                    return true;
                }
            );

        $reflection = new ReflectionProperty($class, 'eventName');
        $reflection->setValue($mock, $eventName);

        $reflection = new ReflectionProperty($class, 'dispatcher');
        $reflection->setValue($mock, $dispatcher);

        return $mock;
    }

    /**
     * Check that the parsing of the callbacks is working.
     *
     * @return void
     */
    public function testCallbackParsing(): void
    {
        $dispatcher = new EventDispatcher();
        $container  = new DefaultContainer('tl_test');
        $event      = new BuildDataDefinitionEvent($container);
        $builder    = $this->mockBuilderWithDca(
            [
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
        self::assertCount(1, $dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME));
        foreach ($dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME) as $listener) {
            /** @var AbstractCallbackListener $listener */
            self::assertTrue($listener->wantToExecute($event));
            $event->setValue('testvalue');
            $listener($event);
            self::assertEquals('executed', $event->getValue());
        }

        $event->setProperty('testProperty2');

        foreach ($dispatcher->getListeners(EncodePropertyValueFromWidgetEvent::NAME) as $listener) {
            /** @var AbstractCallbackListener $listener */
            self::assertFalse($listener->wantToExecute($event));
        }
    }
}
