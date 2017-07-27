<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test the main wrapper class \DC_General that it can be instantiated by Contao.
 */
class DcGeneralTest extends TestCase
{
    /**
     * Test that the \DC_General class in global namespace is found by Contao.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testInstantiation()
    {
        define('TL_MODE', 'BE');
        $_SESSION = array('BE_DATA' => array('DC_GENERAL_TL_FOO' => array()));
        $this->aliasContaoClass('Session');
        $this->aliasContaoClass('System');
        $this->aliasContaoClass('Controller');
        $this->aliasContaoClass('Backend');
        $this->aliasContaoClass('DataContainer');

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(PopulateEnvironmentEvent::NAME, function ($event) {
            if ($event instanceof PopulateEnvironmentEvent) {
                $event->getEnvironment()->setClipboard(
                    $this->getMockForAbstractClass(ClipboardInterface::class)
                );
            }
        });

        $mockDefinitionContainer = $this->getMockForAbstractClass(DataDefinitionContainerInterface::class);
        $mockDefinitionContainer
            ->expects($this->once())
            ->method('hasDefinition')
            ->willReturn(false);

        System::setContainer($container = $this->getMockForAbstractClass(ContainerInterface::class));
        $container
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($name) use ($eventDispatcher, $mockDefinitionContainer) {
                switch ($name) {
                    case 'event_dispatcher': return $eventDispatcher;
                    case 'cca.translator.contao_translator': return new StaticTranslator();
                    case 'cca.dc-general.data-definition-container': return $mockDefinitionContainer;
                }
                return null;
            });

        $GLOBALS['TL_DCA']['tl_foo'] = array(
            'config'          => array
            (
                'dataContainer'    => 'General',
            ),
            'dca_config'   => array
            (
                'data_provider'  => array
                (
                    'tl_foo' => array
                    (
                        'source' => 'tl_foo',
                        'class'  => NoOpDataProvider::class,
                    )
                ),
            ),
            'palettes' => []
        );

        $dataContainer = new \DC_General('tl_foo');

        $this->assertTrue($dataContainer instanceof \DC_General);
        $this->assertTrue($dataContainer instanceof General);
    }
}
