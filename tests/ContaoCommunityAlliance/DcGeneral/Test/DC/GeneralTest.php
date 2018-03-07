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

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\Contao\EventDispatcher\EventDispatcherInitializer;
use ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider;
use ContaoCommunityAlliance\DcGeneral\DC_General;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test the main wrapper class \DC_General that it can be instantiated by Contao.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
// @codingStandardsIgnoreStart
class DC_GeneralTest extends TestCase
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
        $_SESSION = ['BE_DATA' => ['DC_GENERAL_TL_FOO' => []]];
        require_once __DIR__ . '/../../../../../vendor/contao/core/system/helper/interface.php';
        $this->aliasContaoClass('Session');
        $this->aliasContaoClass('System');
        $this->aliasContaoClass('Controller');
        $this->aliasContaoClass('Backend');
        $this->aliasContaoClass('DataContainer');

        $eventDispatcher = new EventDispatcher();
        $container       = $GLOBALS['container'] = new \Pimple([
                'event-dispatcher' => $eventDispatcher,
                'translator'       => new StaticTranslator()
            ]
        );

        $this->assertTrue($container['event-dispatcher'] instanceof EventDispatcher);

        $initializer = new EventDispatcherInitializer();
        $initializer->addListeners(
            $eventDispatcher,
            require_once __DIR__ . '/../../../../../contao/config/event_listeners.php'
        );
        $initializer->addSubscribers(
            $eventDispatcher,
            require_once __DIR__ . '/../../../../../contao/config/event_subscribers.php'
        );

        require_once __DIR__ . '/../../../../../contao/config/services.php';

        $GLOBALS['TL_DCA']['tl_foo'] = [
            'config'          => [
                'dataContainer'    => 'General',
                ],
            'dca_config'   => [
                    'data_provider'  => [
                    'tl_foo' => [
                            'source' => 'tl_foo',
                            'class'        => NoOpDataProvider::class,
                        ]
                    ],
                ],
            'palettes' => []
        ];

        $dataContainer = new \DC_General('tl_foo');

        $this->assertTrue($dataContainer instanceof \DC_General);
        $this->assertTrue($dataContainer instanceof DC_General);
    }
}
// @codingStandardsIgnoreEnd
