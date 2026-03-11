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

use Closure;
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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\Event;

use function method_exists;

/**
 * Test for AbstractReturningPropertyCallbackListenerTest
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(BuildWidgetEvent::class, 'getEnvironment')]
#[CoversMethod(BuildWidgetEvent::class, 'getProperty')]
#[CoversMethod(DecodePropertyValueForWidgetEvent::class, 'getEnvironment')]
#[CoversMethod(DecodePropertyValueForWidgetEvent::class, 'getProperty')]
#[CoversMethod(EncodePropertyValueFromWidgetEvent::class, 'getEnvironment')]
#[CoversMethod(EncodePropertyValueFromWidgetEvent::class, 'getProperty')]
#[CoversMethod(GetPropertyOptionsEvent::class, 'getEnvironment')]
#[CoversMethod(GetPropertyOptionsEvent::class, 'getPropertyName')]
#[CoversMethod(ManipulateWidgetEvent::class, 'getEnvironment')]
#[CoversMethod(ManipulateWidgetEvent::class, 'getProperty')]
#[CoversMethod(ModelOptionsCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PropertyInputFieldCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PropertyInputFieldGetWizardCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PropertyInputFieldGetXLabelCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PropertyOnLoadCallbackListener::class, 'wantToExecute')]
#[CoversMethod(PropertyOnSaveCallbackListener::class, 'wantToExecute')]
final class AbstractReturningPropertyCallbackListenerTest extends TestCase
{
    protected function getCallback($value): Closure
    {
        return static function () use ($value) {
            throw new RuntimeException('The callback should not be executed as it is only mocked: ' . $value);
        };
    }

    protected function mockEnvironment($dataContainerName): DefaultEnvironment
    {
        $environment = new DefaultEnvironment();
        $environment->setDataDefinition(new DefaultContainer($dataContainerName));

        return $environment;
    }

    protected function mockPropertyEvent($class, $tablename, $propertyName): MockObject&Event
    {
        if (method_exists($class, 'getProperty')) {
            /** @var MockObject&Event $event */
            $event = $this
                ->getMockBuilder($class)
                ->onlyMethods(['getEnvironment', 'getProperty'])
                ->disableOriginalConstructor()
                ->getMock();

            $event
                ->method('getProperty')
                ->willReturn($propertyName);
        } else {
            /** @var MockObject&Event $event */
            $event = $this
                ->getMockBuilder($class)
                ->onlyMethods(['getEnvironment', 'getPropertyName'])
                ->disableOriginalConstructor()
                ->getMock();
            $event
                ->method('getPropertyName')
                ->willReturn($propertyName);
        }

        $event
            ->method('getEnvironment')
            ->willReturn($this->mockEnvironment($tablename));

        return $event;
    }


    public static function propertyCallbackDataProvider(): array
    {
        return [
            [
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

    #[Dataprovider('propertyCallbackDataProvider')]
    public function testExecution($listenerClass, $eventClass): void
    {
        $listener = new $listenerClass($this->getCallback($listenerClass), ['tablename', 'propertyName']);
        self::assertTrue(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'tablename', 'propertyName')),
            $listenerClass
        );
        self::assertFalse(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'anotherTable', 'propertyName')),
            $listenerClass
        );
        self::assertFalse(
            $listener->wantToExecute($this->mockPropertyEvent($eventClass, 'tablename', 'anotherPropertyName')),
            $listenerClass
        );
    }
}
