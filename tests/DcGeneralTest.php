<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hopes to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use DC_General;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function define;

/**
 * Test the main wrapper class \DC_General that it can be instantiated by Contao.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(General::class)]
final class DcGeneralTest extends TestCase
{
    /**
     * Test that the \DC_General class in the global namespace is found by Contao.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testInstantiation(): void
    {
        define('TL_MODE', 'BE');
        $_SESSION = ['BE_DATA' => ['DC_GENERAL_TL_FOO' => [], 'DC_GENERAL_TL_BAR' => []]];

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(PopulateEnvironmentEvent::NAME, function ($event) {
            if ($event instanceof PopulateEnvironmentEvent) {
                $event->getEnvironment()->setClipboard(
                    $this->getMockBuilder(ClipboardInterface::class)->getMock()
                );
            }
        });

        $definitionContainer = $this->getMockBuilder(DataDefinitionContainerInterface::class)->getMock();
        $definitionContainer
            ->method('hasDefinition')
            ->willReturn(false);

        System::setContainer($container = $this->getMockBuilder(ContainerInterface::class)->getMock());
        $container
            ->method('get')
            ->willReturnCallback(function ($name) use ($eventDispatcher, $definitionContainer) {
                switch ($name) {
                    case 'event_dispatcher':
                        return $eventDispatcher;
                    case 'cca.translator.contao_translator':
                        return new StaticTranslator();
                    case 'cca.dc-general.data-definition-container':
                        return $definitionContainer;
                }
                return null;
            });

        $GLOBALS['TL_DCA']['tl_foo'] = [
            'config'     => [
                'dataContainer' => 'General'
            ],
            'dca_config' => [
                'data_provider' => [
                    'default' => [
                        'source' => 'tl_foo',
                        'class'  => NoOpDataProvider::class
                    ]
                ],
            ],
            'palettes'   => []
        ];

        $GLOBALS['TL_DCA']['tl_bar'] = [
            'config'     => [
                'dataContainer' => 'General',
            ],
            'dca_config' => [
                'data_provider' => [
                    'default' => [
                        'source' => 'tl_bar',
                        'class'  => NoOpDataProvider::class
                    ],
                    'parent' => [
                        'source' => 'tl_foo',
                        'class'  => NoOpDataProvider::class
                    ]
                ],
            ],
            'palettes'   => []
        ];

        $cache = new ArrayAdapter();

        $dataContainerFoo = new DC_General('tl_foo', [], $cache);

        $dataContainerBar = new DC_General('tl_bar', [], $cache);

        self::assertInstanceOf(
            EnvironmentInterface::class,
            $dataContainerBar->getEnvironment()->setParentDataDefinition(
                $dataContainerFoo->getEnvironment()->getDataDefinition()
            )
        );


        self::assertInstanceOf(DC_General::class, $dataContainerFoo);
        self::assertInstanceOf(General::class, $dataContainerFoo);

        self::assertInstanceOf(DC_General::class, $dataContainerBar);
        self::assertInstanceOf(General::class, $dataContainerBar);
    }
}
