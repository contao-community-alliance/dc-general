<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\Contao\EventDispatcher\EventDispatcherInitializer;
use ContaoCommunityAlliance\DcGeneral\DC_General;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test the main wrapper class \DC_General that it can be instantiated by Contao.
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
        $_SESSION = array('BE_DATA' => array('DC_GENERAL_TL_FOO' => array()));
        require_once __DIR__ . '/../../../../vendor/contao/core/system/helper/interface.php';
        $this->aliasContaoClass('Session');
        $this->aliasContaoClass('System');
        $this->aliasContaoClass('Controller');
        $this->aliasContaoClass('Backend');
        $this->aliasContaoClass('DataContainer');

        $eventDispatcher = new EventDispatcher();
        $container       = $GLOBALS['container'] = new \Pimple(array(
            'event-dispatcher' => $eventDispatcher,
            'translator'       => new StaticTranslator()
        ));

        $this->assertTrue($container['event-dispatcher'] instanceof EventDispatcher);

        $initializer = new EventDispatcherInitializer();
        $initializer->addListeners(
            $eventDispatcher,
            require_once __DIR__ . '/../../../../contao/config/event_listeners.php'
        );
        $initializer->addSubscribers(
            $eventDispatcher,
            require_once __DIR__ . '/../../../../contao/config/event_subscribers.php'
        );

        require_once __DIR__ . '/../../../../contao/config/services.php';

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
                        'class'        => 'ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider',
                    )
                ),
            )
        );

        $dataContainer = new \DC_General('tl_foo');

        $this->assertTrue($dataContainer instanceof \DC_General);
        $this->assertTrue($dataContainer instanceof DC_General);
    }
}
// @codingStandardsIgnoreEnd
