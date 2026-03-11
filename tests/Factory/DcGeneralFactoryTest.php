<?php

/**
 * This file is part of contao-community-alliance/dc-general-contao-frontend.
 *
 * (c) 2015-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general-contao-frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2015-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Factory;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(DcGeneralFactory::class)]
final class DcGeneralFactoryTest extends TestCase
{
    public function testCreateDcGeneral(): void
    {
        $eventDispatcher = new EventDispatcher();
        $mockTranslator = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        System::setContainer($container = $this->getMockBuilder(ContainerInterface::class)->getMock());

        $definitionContainer = $this->getMockBuilder(DataDefinitionContainerInterface::class)->getMock();
        $container
            ->expects($this->once())
            ->method('get')
            ->with('cca.dc-general.data-definition-container')
            ->willReturn($definitionContainer);

        $definitionContainer
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('test-container')
            ->willReturn(false);

        $cache = new ArrayAdapter();

        /** @var TranslatorInterface $mockTranslator */
        $factory   = new DcGeneralFactory($cache);
        $dcGeneral = $factory
            ->setContainerName('test-container')
            ->setEventDispatcher($eventDispatcher)
            ->setTranslator($mockTranslator)
            ->createDcGeneral();

        self::assertInstanceOf(EnvironmentInterface::class, $dcGeneral->getEnvironment());
    }
}
