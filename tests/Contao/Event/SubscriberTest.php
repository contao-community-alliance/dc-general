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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Event;

use Contao\Config;
use Contao\Date;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\Twig\DcGeneralExtension;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPanelElementTemplateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ListView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ParentView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreeView;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultPanelContainer;
use ContaoCommunityAlliance\DcGeneral\Panel\FilterElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\LimitElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SearchElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SubmitElementInterface;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\DcGeneral\Contao\Contao2BackendView\NonBaseView;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This class test the subscriber.
 *
 * @see  \ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber
 */
class SubscriberTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['TL_CONFIG']['characterSet'] = 'utf-8';
        \define('TL_ROOT', __DIR__ . '/../../../../../../vendor/contao/core');
        require __DIR__ . '/../../../../../../vendor/contao/core-bundle/src/Resources/contao/config/default.php';
        require __DIR__ . '/../../../../../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';


        self::initializeContaoConfig();
        self::initializeContaoController();
        self::initializeContaoTwig();
        self::initializeContaoTemplate();
        self::initializeContaoBackendTemplate();
        parent::setUpBeforeClass();
    }

    public function testGetSubscribedEvents()
    {
        $events = Subscriber::getSubscribedEvents();

        $this->assertSame('array', \gettype($events));
        $this->assertArrayHasKey(DcGeneralEvents::ACTION, $events);
        $this->assertArrayHasKey(GetPanelElementTemplateEvent::NAME, $events);
        $this->assertArrayHasKey(ResolveWidgetErrorMessageEvent::NAME, $events);
        $this->assertArrayHasKey(RenderReadablePropertyValueEvent::NAME, $events);
        $this->assertArrayHasKey('contao-twig.init', $events);
    }

    public function getPanelElementTemplateDataProvider()
    {
        return [
            ['has_template'],
            ['dcbe_general_panel_filter', FilterElementInterface::class],
            ['dcbe_general_panel_limit', LimitElementInterface::class],
            ['dcbe_general_panel_search', SearchElementInterface::class],
            ['dcbe_general_panel_sort', SortElementInterface::class],
            ['dcbe_general_panel_submit', SubmitElementInterface::class]
        ];
    }

    /**
     * @dataProvider getPanelElementTemplateDataProvider
     */
    public function testGetPanelElementTemplate($excepted, $element = null)
    {
        $dispatcher = new EventDispatcher();

        if (null === $element) {
            $panelElement = $this->getMockForAbstractClass(PanelElementInterface::class);
        } else {
            $panelElement = $this->getMockForAbstractClass($element);
        }
        $event = new GetPanelElementTemplateEvent(new DefaultEnvironment(), $panelElement);

        if (null === $element) {
            $event->setTemplate(new ContaoBackendViewTemplate($excepted));
        }

        $scopeDeterminator = $this->mockScopeDeterminator();

        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'getPanelElementTemplate']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($excepted, $event->getTemplate()->getName());
    }

    public function widgetErrorMessageDataProvider()
    {
        $exceptionError      = new \Exception('foo');
        $objectError         = $this->getMockBuilder('stdClass')->getMock();
        $objectErrorToString = $this->getMockBuilder('stdClass')->setMethods(['__toString'])->getMock();
        $objectErrorToString
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('foo__toString');

        return [
            [$exceptionError, 'foo'],
            [$objectError, '[' . \get_class($objectError) . ']'],
            [$objectErrorToString, 'foo__toString'],
            [false, '[boolean]'],
            [1, '[integer]'],
            [['foo'], '[array]']
        ];
    }

    /**
     * @dataProvider widgetErrorMessageDataProvider
     */
    public function testResolveWidgetErrorMessage($error, $excepted)
    {
        $dispatcher = new EventDispatcher();
        $event      = new ResolveWidgetErrorMessageEvent(new DefaultEnvironment(), $error);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'resolveWidgetErrorMessage']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($excepted, $event->getError());
    }

    public function testRenderReadablePropertyValueIsRendered()
    {
        $dispatcher = new EventDispatcher();

        $event = new RenderReadablePropertyValueEvent(
            new DefaultEnvironment(),
            new DefaultModel(),
            new DefaultProperty('fooProperty'),
            'fooValue'
        );

        $event->setRendered('foo');

        $scopeDeterminator = $this->mockScopeDeterminator();

        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame('foo', $event->getRendered());
    }

    public function testRenderForeignKeyReadable()
    {
        $event = $this->setupRenderReadablePropertyValueEvent(
            'testValue',
            'testProperty',
            ['foreignKey' => 'testForeignKey']
        );

        $scopeDeterminator = $this->mockScopeDeterminator();

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame('testValue', $event->getValue());
        $this->assertNull($event->getRendered());
    }

    public function arrayReadableSingleDataProvider()
    {
        return [
            [[3, 2, 1], '3, 2, 1'],
            [[3 => ['foo', 'bar'], 2, 1], 'foo (bar), 2, 1']
        ];
    }

    /**
     * @dataProvider arrayReadableSingleDataProvider
     */
    public function testRenderArrayReadableSingle($values, $excepted)
    {
        $event = $this->setupRenderReadablePropertyValueEvent($values, 'testProperty');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($values, $event->getValue());
        $this->assertSame($excepted, $event->getRendered());
    }

    public function timestampReadableForDateDataProvider()
    {
        $date = new \DateTime();

        return [
            ['non-format', $date->getTimestamp(), $date->getTimestamp()],
            ['datim', $date->getTimestamp(), $date->format('Y-m-d H:i')],
            ['date', $date->getTimestamp(), $date->format('Y-m-d')],
            ['time', $date->getTimestamp(), $date->format('H:i')]
        ];
    }

    /**
     * @dataProvider timestampReadableForDateDataProvider
     */
    public function testRenderTimestampReadableForDate($format, $time, $excepted)
    {
        $this->runTimestampReadable('testProperty', $time, $excepted, ['rgxp' => $format]);
    }

    public function testRenderPropertyTimestampReadable()
    {
        $date = new \DateTime();
        $this->runTimestampReadable('tstamp', $date->getTimestamp(), $date->format('H:i'));
    }

    public function testRenderValueIsDateTimeReadable()
    {
        $dateTime = new \DateTime();

        $event = $this->setupRenderReadablePropertyValueEvent($dateTime, 'testProperty');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener($this->once());

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);
        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($dateTime, $event->getValue());
        $this->assertSame($dateTime->format('Y-m-d H:i'), $event->getRendered());
    }

    public function widgetCheckBoxReadableDataProvider()
    {
        return [
            [true, 'MSC.yes'],
            [false, 'MSC.no']
        ];
    }

    /**
     * @dataProvider widgetCheckBoxReadableDataProvider
     */
    public function testRenderWidgetCheckBoxReadable($value, $excepted)
    {
        $event = $this->setupRenderReadablePropertyValueEvent($value, 'testProperty');
        $event->getProperty()->setWidgetType('checkbox');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($value, $event->getValue());
        $this->assertSame($excepted, $event->getRendered());
    }

    public function widgetTextAreaReadableDataProvider()
    {
        $propertyExtra1 = [
            'allowHtml'    => true,
            'preserveTags' => true
        ];

        $propertyExtra2 = [
            'allowHtml' => true
        ];

        $propertyExtra3 = [
            'preserveTags' => true
        ];

        return [
            ['<p>foo</p>', 'testProperty', $propertyExtra1, 'textarea', '&lt;p&gt;foo&lt;/p&gt;'],
            ['<p>foo</p>', 'testProperty', $propertyExtra2, 'textarea', '&lt;p&gt;foo&lt;/p&gt;'],
            ['<p>foo</p>', 'testProperty', $propertyExtra3, 'textarea', '&lt;p&gt;foo&lt;/p&gt;'],
            ['<p>foo</p>', 'testProperty', [], 'textarea', null]
        ];
    }

    /**
     * @dataProvider widgetTextAreaReadableDataProvider
     */
    public function testRenderWidgetTextAreaReadable($value, $propertyName, $propertyExtra, $widgetType, $excepted)
    {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName, $propertyExtra);
        $event->getProperty()->setWidgetType($widgetType);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        if (null === $excepted) {
            $this->assertSame($value, $event->getValue());
            $this->assertNull($event->getRendered());

            return;
        }

        $this->assertSame($value, $event->getValue());
        $this->assertSame($excepted, $event->getRendered());
    }

    public function referenceReadableDataProvider()
    {
        $propertyExtra1 = [
            'reference' => ''
        ];

        $propertyExtra2 = [
            'reference' => ['foo' => 'bar']
        ];

        $propertyExtra3 = [
            'reference' => ['referenceValue' => ['referenceValue']]
        ];

        $propertyExtra4 = [
            'reference' => ['referenceValue' => 'renderedReferenceValue']
        ];

        return [
            ['referenceValue', 'testProperty', $propertyExtra1, null],
            ['referenceValue', 'testProperty', $propertyExtra2, null],
            ['referenceValue', 'testProperty', $propertyExtra3, 'referenceValue'],
            ['referenceValue', 'testProperty', $propertyExtra4, 'renderedReferenceValue']
        ];
    }

    /**
     * @dataProvider referenceReadableDataProvider
     */
    public function testRenderReferenceReadable($value, $propertyName, $propertyExtra, $excepted)
    {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName, $propertyExtra);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($value, $event->getValue());

        if (null === $excepted) {
            $this->assertNull($event->getRendered());

            return;
        }

        $this->assertSame($excepted, $event->getRendered());
    }

    public function optionValueReadableDataProvider()
    {
        return [
            ['testValue', 'testProperty', null],
            ['testValue', 'testProperty', 'renderedTestValue', ['testValue' => 'renderedTestValue']],
            ['testValue', 'testProperty', 'renderedTestValue', ['testValue' => 'renderedTestValue'], true],
        ];
    }

    /**
     * @dataProvider optionValueReadableDataProvider
     */
    public function testOptionValueReadable(
        $value,
        $propertyName,
        $excepted,
        array $propertyOptions = [],
        $optionsForListener = false
    ) {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName);
        if (!empty($propertyOptions) && !$optionsForListener) {
            $event->getProperty()->setOptions($propertyOptions);
        }

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener($this->never());

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);

        if (!empty($propertyOptions) && $optionsForListener) {
            $optionsListener = $this->mockGetPropertyOptionsEventListener($this->once(), $propertyOptions);
            $dispatcher->addListener(GetPropertyOptionsEvent::NAME, [$optionsListener, 'handle']);
        }

        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($value, $event->getValue());

        if (null === $excepted) {
            $this->assertNull($event->getRendered());
            return;
        }

        $this->assertSame($excepted, $event->getRendered());
    }

    public function testInitTwig()
    {
        $this->markTestSkipped('The Contao twig extention we not using at time. We show for replace this.');
        return;

        $dispatcher = new EventDispatcher();

        $contaoTwig = \ContaoTwig::getInstance();

        $event = new \ContaoTwigInitializeEvent($contaoTwig);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener('contao-twig.init', [new Subscriber($scopeDeterminator), 'initTwig']);
        $dispatcher->dispatch('contao-twig.init', $event);

        $environment = $contaoTwig->getEnvironment();

        $this->assertTrue($environment->hasExtension('dc-general'));
        $this->assertInstanceOf(DcGeneralExtension::class, $environment->getExtension('dc-general'));
    }

    public function initializePanelsDataProvider()
    {
        return [
            ['select', NonBaseView::class, [], [1, 2]],
            ['select', BaseView::class, [$this->mockScopeDeterminator()], [1, 2]],
            ['select', ListView::class, [$this->mockScopeDeterminator()], [1, 2]],
            ['select', ParentView::class, [$this->mockScopeDeterminator()], [1, 2]],
            ['select', TreeView::class, [$this->mockScopeDeterminator()], [1, 2]],

            ['copy', NonBaseView::class, [], [1, 2]],
            ['copy', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['copy', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['copy', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['copy', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['create', NonBaseView::class, [], [1, 2]],
            ['create', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['create', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['create', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['create', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['paste', NonBaseView::class, [], [1, 2]],
            ['paste', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['paste', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['paste', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['paste', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['delete', NonBaseView::class, [], [1, 2]],
            ['delete', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['delete', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['delete', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['delete', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['move', NonBaseView::class, [], [1, 2]],
            ['move', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['move', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['move', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['move', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['undo', NonBaseView::class, [], [1, 2]],
            ['undo', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['undo', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['undo', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['undo', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['edit', NonBaseView::class, [], [1, 2]],
            ['edit', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['edit', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['edit', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['edit', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['toggle', NonBaseView::class, [], [1, 2]],
            ['toggle', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['toggle', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['toggle', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['toggle', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['showAll', NonBaseView::class, [], [1, 2]],
            ['showAll', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['showAll', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['showAll', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['showAll', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],

            ['show', NonBaseView::class, [], [1, 2]],
            ['show', BaseView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['show', ListView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['show', ParentView::class, [$this->mockScopeDeterminator()], [3, 4]],
            ['show', TreeView::class, [$this->mockScopeDeterminator()], [3, 4]],
        ];
    }

    /**
     * @dataProvider initializePanelsDataProvider
     */
    public function testInitializePanels($actionName, $viewClass, $constructor, $excepted)
    {
        $dispatcher = new EventDispatcher();

        $action      = new Action($actionName);
        $environment = new DefaultEnvironment();
        $event       = new ActionEvent($environment, $action);

        $dataDefinition = new DefaultContainer('foo');
        $environment->setDataDefinition($dataDefinition);

        $baseConfigRegistry = $this
            ->getMockBuilder(BaseConfigRegistry::class)
            ->setMethods(['getBaseConfig'])
            ->getMock();
        $environment->setBaseConfigRegistry($baseConfigRegistry);

        $dataConfig = DefaultConfig::init();
        $dataConfig->setSorting([1, 2]);
        $baseConfigRegistry
            ->method('getBaseConfig')
            ->willReturn($dataConfig);

        $backendView = new Contao2BackendViewDefinition();
        $dataDefinition->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $backendView);

        $view = $this
            ->getMockBuilder($viewClass)
            ->setConstructorArgs($constructor)
            ->setMethods(['getPanel'])
            ->getMock();
        $environment->setView($view);

        $panel = $this->getMockBuilder(DefaultPanelContainer::class)->setMethods(['initialize'])->getMock();
        $view
            ->method('getPanel')
            ->willReturn($panel);

        $panel
            ->method('initialize')
            ->will(
                $this->returnCallback(
                    function ($config) {
                        $config->setSorting([3, 4]);
                    }
                )
            );

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener(DcGeneralEvents::ACTION, [new Subscriber($scopeDeterminator), 'initializePanels']);
        $dispatcher->dispatch(DcGeneralEvents::ACTION, $event);

        $this->assertArraySubset($dataConfig->getSorting(), $excepted);
    }

    public function testGetConfig()
    {
        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);

        $this->assertInstanceOf(Config::class, Subscriber::getConfig());
        $this->assertInstanceOf(Config::class, $subscriber::getConfig());
    }

    public function testSetConfig()
    {
        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);

        $subscriber::setConfig(Config::getInstance());

        $this->assertInstanceOf(Config::class, $subscriber::getConfig());
    }

    private function runTimestampReadable($propertyName, $time, $excepted, array $extra = [])
    {
        $event = $this->setupRenderReadablePropertyValueEvent($time, $propertyName, $extra);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener(
            (isset($extra['rgxp']) && $extra['rgxp'] === 'non-format') ? $this->never() : $this->once()
        );

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);
        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event::NAME, $event);

        $this->assertSame($time, $event->getValue());

        if (isset($extra['rgxp']) && $extra['rgxp'] === 'non-format') {
            $this->assertNull($event->getRendered());

            return;
        }

        $this->assertSame($excepted, $event->getRendered());
    }

    private function setupRenderReadablePropertyValueEvent($value, $propertyName, array $extra = [])
    {
        $defaultExtra = [
            'multiple' => null
        ];

        $property = new DefaultProperty($propertyName);
        $property->setExtra(\array_merge($defaultExtra, $extra));

        $environment = new DefaultEnvironment();
        $environment->setEventDispatcher(new EventDispatcher());
        $environment->setTranslator(new TranslatorChain());

        return new RenderReadablePropertyValueEvent(
            $environment,
            new DefaultModel(),
            $property,
            $value
        );
    }

    private function mockParseDateEventListener($expects)
    {
        $listener = $this
            ->getMockBuilder('stdClass')
            ->setMethods(['handle'])
            ->getMock();
        $listener
            ->expects($expects)
            ->method('handle')
            ->with(
                $this->isInstanceOf(ParseDateEvent::class),
                $this->equalTo(ContaoEvents::DATE_PARSE),
                $this->isInstanceOf(EventDispatcher::class)
            )
            ->will(
                $this->returnCallback(
                    function (ParseDateEvent $event) {
                        $event->setResult(Date::parse($event->getFormat(), $event->getTimestamp()));
                    }
                )
            );

        return $listener;
    }

    private function mockGetPropertyOptionsEventListener($expects, $options)
    {
        $listener = $this
            ->getMockBuilder('stdClass')
            ->setMethods(['handle'])
            ->getMock();
        $listener
            ->expects($expects)
            ->method('handle')
            ->with(
                $this->isInstanceOf(GetPropertyOptionsEvent::class),
                $this->equalTo(GetPropertyOptionsEvent::NAME),
                $this->isInstanceOf(EventDispatcher::class)
            )
            ->will(
                $this->returnCallback(
                    function (GetPropertyOptionsEvent $event) use ($options) {
                        if (!\count($options)) {
                            return;
                        }

                        $event->setOptions($options);
                    }
                )
            );

        return $listener;
    }

    private function mockScopeDeterminator()
    {
        $scopeDeterminator = $this
            ->getMockBuilder(RequestScopeDeterminator::class)
            ->setMethods(['currentScopeIsBackend'])
            ->disableOriginalConstructor()
            ->getMock();

        $scopeDeterminator
            ->method('currentScopeIsBackend')
            ->willReturn(true);

        return $scopeDeterminator;
    }
}
