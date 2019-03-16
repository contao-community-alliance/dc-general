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

        $this->assertNull($environment->getController());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setController($controller));
        $this->assertInstanceOf(ControllerInterface::class, $environment->getController());
        $this->assertSame($controller, $environment->getController());

        $this->assertNull($environment->getView());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setView($view));
        $this->assertInstanceOf(ViewInterface::class, $environment->getView());
        $this->assertSame($view, $environment->getView());

        $this->assertNull($environment->getDataDefinition());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setDataDefinition($container));
        $this->assertInstanceOf(ContainerInterface::class, $environment->getDataDefinition());
        $this->assertSame($container, $environment->getDataDefinition());

        $this->assertNull($environment->getParentDataDefinition());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setParentDataDefinition($parentContainer));
        $this->assertInstanceOf(ContainerInterface::class, $environment->getParentDataDefinition());

        $this->assertNull($environment->getRootDataDefinition());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setRootDataDefinition($rootContainer));
        $this->assertInstanceOf(ContainerInterface::class, $environment->getRootDataDefinition());
        $this->assertSame($rootContainer, $environment->getRootDataDefinition());

        $this->assertNull($environment->getSessionStorage());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setSessionStorage($sessionStorage));
        $this->assertInstanceOf(SessionStorageInterface::class, $environment->getSessionStorage());
        $this->assertSame($sessionStorage, $environment->getSessionStorage());

        $this->assertNull($environment->getInputProvider());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setInputProvider($inputProvider));
        $this->assertInstanceOf(InputProviderInterface::class, $environment->getInputProvider());
        $this->assertSame($inputProvider, $environment->getInputProvider());

        $this->assertNull($environment->getBaseConfigRegistry());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setBaseConfigRegistry($baseConfig));
        $this->assertInstanceOf(BaseConfigRegistryInterface::class, $environment->getBaseConfigRegistry());
        $this->assertSame($baseConfig, $environment->getBaseConfigRegistry());

        $this->assertNull($environment->getClipboard());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setClipboard($clipBoard));
        $this->assertInstanceOf(ClipboardInterface::class, $environment->getClipboard());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setClipboard(null));
        $this->assertNull($environment->getClipboard());

        $this->assertNull($environment->getTranslator());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setTranslator($translator));
        $this->assertInstanceOf(TranslatorInterface::class, $environment->getTranslator());
        $this->assertSame($translator, $environment->getTranslator());

        $this->assertNull($environment->getEventDispatcher());
        $this->assertInstanceOf(DefaultEnvironment::class, $environment->setEventDispatcher($eventDispatcher));
        $this->assertInstanceOf(EventDispatcherInterface::class, $environment->getEventDispatcher());
        $this->assertSame($eventDispatcher, $environment->getEventDispatcher());
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
            $this->assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            $this->assertSame('Data provider foo not defined', $exception->getMessage());
        }

        $environment->addDataProvider('foo', $fooDataProvider);
        $this->assertTrue($environment->hasDataProvider());
        $this->assertInstanceOf(DataProviderInterface::class, $environment->getDataProvider());
        $this->assertSame($fooDataProvider, $environment->getDataProvider());
        $this->assertTrue($environment->hasDataProvider('foo'));
        $this->assertInstanceOf(DataProviderInterface::class, $environment->getDataProvider('foo'));
        $this->assertSame($fooDataProvider, $environment->getDataProvider('foo'));

        $environment->removeDataProvider('foo');
        $this->assertFalse($environment->hasDataProvider('foo'));
    }
}
