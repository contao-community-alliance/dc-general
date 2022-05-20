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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test all methods for the default environment.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\DefaultEnvironment
 */
class DefaultEnvironmentTest extends TestCase
{
    public function testSetterAndGetter()
    {
        $controller      = $this->getMockBuilder(ControllerInterface::class)->getMock();
        $view            = $this->getMockBuilder(ViewInterface::class)->getMock();
        $container       = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $parentContainer = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $rootContainer   = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $sessionStorage  = $this->getMockBuilder(SessionStorageInterface::class)->getMock();
        $inputProvider   = $this->getMockBuilder(InputProviderInterface::class)->getMock();
        $baseConfig      = $this->getMockBuilder(BaseConfigRegistryInterface::class)->getMock();
        $clipBoard       = $this->getMockBuilder(ClipboardInterface::class)->getMock();
        $translator      = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $environment = new DefaultEnvironment();

        self::assertNull($environment->getController());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setController($controller));
        self::assertInstanceOf(ControllerInterface::class, $environment->getController());
        self::assertSame($controller, $environment->getController());

        self::assertNull($environment->getView());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setView($view));
        self::assertInstanceOf(ViewInterface::class, $environment->getView());
        self::assertSame($view, $environment->getView());

        self::assertNull($environment->getDataDefinition());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setDataDefinition($container));
        self::assertInstanceOf(ContainerInterface::class, $environment->getDataDefinition());
        self::assertSame($container, $environment->getDataDefinition());

        self::assertNull($environment->getParentDataDefinition());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setParentDataDefinition($parentContainer));
        self::assertInstanceOf(ContainerInterface::class, $environment->getParentDataDefinition());

        self::assertNull($environment->getRootDataDefinition());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setRootDataDefinition($rootContainer));
        self::assertInstanceOf(ContainerInterface::class, $environment->getRootDataDefinition());
        self::assertSame($rootContainer, $environment->getRootDataDefinition());

        self::assertNull($environment->getSessionStorage());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setSessionStorage($sessionStorage));
        self::assertInstanceOf(SessionStorageInterface::class, $environment->getSessionStorage());
        self::assertSame($sessionStorage, $environment->getSessionStorage());

        self::assertNull($environment->getInputProvider());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setInputProvider($inputProvider));
        self::assertInstanceOf(InputProviderInterface::class, $environment->getInputProvider());
        self::assertSame($inputProvider, $environment->getInputProvider());

        self::assertNull($environment->getBaseConfigRegistry());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setBaseConfigRegistry($baseConfig));
        self::assertInstanceOf(BaseConfigRegistryInterface::class, $environment->getBaseConfigRegistry());
        self::assertSame($baseConfig, $environment->getBaseConfigRegistry());

        self::assertNull($environment->getClipboard());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setClipboard($clipBoard));
        self::assertInstanceOf(ClipboardInterface::class, $environment->getClipboard());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setClipboard(null));
        self::assertNull($environment->getClipboard());

        self::assertNull($environment->getTranslator());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setTranslator($translator));
        self::assertInstanceOf(TranslatorInterface::class, $environment->getTranslator());
        self::assertSame($translator, $environment->getTranslator());

        self::assertNull($environment->getEventDispatcher());
        self::assertInstanceOf(DefaultEnvironment::class, $environment->setEventDispatcher($eventDispatcher));
        self::assertInstanceOf(EventDispatcherInterface::class, $environment->getEventDispatcher());
        self::assertSame($eventDispatcher, $environment->getEventDispatcher());
    }

    public function testDataProvider()
    {
        $basicDefinition = $this
            ->getMockBuilder(DefaultBasicDefinition::class)
            ->setMethods(['getDataProvider'])
            ->getMock();
        $basicDefinition->method('getDataProvider')->willReturn('foo');

        $container = $this
            ->getMockBuilder(DefaultContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBasicDefinition'])
            ->getMock();
        $container->method('getBasicDefinition')->willReturn($basicDefinition);

        $fooDataProvider = $this->getMockBuilder(DataProviderInterface::class)->getMock();

        $environment = new DefaultEnvironment();
        $environment->setDataDefinition($container);

        try {
            $environment->getDataProvider();
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame('Data provider foo not defined', $exception->getMessage());
        }

        $environment->addDataProvider('foo', $fooDataProvider);
        self::assertTrue($environment->hasDataProvider());
        self::assertInstanceOf(DataProviderInterface::class, $environment->getDataProvider());
        self::assertSame($fooDataProvider, $environment->getDataProvider());
        self::assertTrue($environment->hasDataProvider('foo'));
        self::assertInstanceOf(DataProviderInterface::class, $environment->getDataProvider('foo'));
        self::assertSame($fooDataProvider, $environment->getDataProvider('foo'));

        $environment->removeDataProvider('foo');
        self::assertFalse($environment->hasDataProvider('foo'));
    }
}
