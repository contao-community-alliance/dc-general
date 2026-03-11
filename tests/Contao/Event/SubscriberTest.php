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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
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
use ContaoTwig;
use ContaoTwigInitializeEvent;
use DateTime;
use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_merge;
use function get_class;
use function gettype;

/**
 * This class test the subscriber.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Subscriber::class)]
final class SubscriberTest extends TestCase
{
    /** @SuppressWarnings(PHPMD.Superglobals) */
    public static function setUpBeforeClass(): void
    {
        // $GLOBALS['TL_CONFIG']['characterSet'] = 'utf-8';
        // \define('TL_ROOT', __DIR__ . '/../../../../../../vendor/contao/core');
        // require __DIR__ . '/../../../vendor/contao/core-bundle/src/Resources/contao/config/default.php';
        // require __DIR__ . '/../../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';

        $GLOBALS['TL_CONFIG']['datimFormat'] = 'Y-m-d H:i';
        $GLOBALS['TL_CONFIG']['dateFormat'] = 'Y-m-d';
        $GLOBALS['TL_CONFIG']['timeFormat'] = 'H:i';

        self::initializeContaoConfig();
        self::initializeContaoController();
        self::initializeContaoTwig();
        self::initializeContaoTemplate();
        self::initializeContaoBackendTemplate();
        parent::setUpBeforeClass();
    }

    public function testGetSubscribedEvents(): void
    {
        $events = Subscriber::getSubscribedEvents();

        self::assertSame('array', gettype($events));
        self::assertArrayHasKey(DcGeneralEvents::ACTION, $events);
        self::assertArrayHasKey(GetPanelElementTemplateEvent::NAME, $events);
        self::assertArrayHasKey(ResolveWidgetErrorMessageEvent::NAME, $events);
        self::assertArrayHasKey(RenderReadablePropertyValueEvent::NAME, $events);
        self::assertArrayHasKey('contao-twig.init', $events);
    }

    public static function getPanelElementTemplateDataProvider(): array
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

    #[Dataprovider('getPanelElementTemplateDataProvider')]
    public function testGetPanelElementTemplate($excepted, $element = null): void
    {
        $dispatcher = new EventDispatcher();

        if (null === $element) {
            $panelElement = $this->getMockBuilder(PanelElementInterface::class)->getMock();
        } else {
            $panelElement = $this->getMockBuilder($element)->getMock();
        }
        $event = new GetPanelElementTemplateEvent(new DefaultEnvironment(), $panelElement);

        if (null === $element) {
            $event->setTemplate(new ContaoBackendViewTemplate($excepted));
        }

        $scopeDeterminator = $this->mockScopeDeterminator();

        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'getPanelElementTemplate']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($excepted, $event->getTemplate()->getName());
    }

    public static function widgetErrorMessageDataProvider(): array
    {
        $exceptionError      = new Exception('foo');
        $objectError         = new class () {
        };
        $objectErrorToString = new class () {
            public function __toString()
            {
                return 'foo__toString';
            }
        };

        return [
            [$exceptionError, 'foo'],
            [$objectError, '[' . get_class($objectError) . ']'],
            [$objectErrorToString, 'foo__toString'],
            [false, '[boolean]'],
            [1, '[integer]'],
            [['foo'], '[array]']
        ];
    }

    #[Dataprovider('widgetErrorMessageDataProvider')]
    public function testResolveWidgetErrorMessage($error, $excepted): void
    {
        $dispatcher = new EventDispatcher();
        $event      = new ResolveWidgetErrorMessageEvent(new DefaultEnvironment(), $error);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'resolveWidgetErrorMessage']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($excepted, $event->getError());
    }

    public function testRenderReadablePropertyValueIsRendered(): void
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
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame('foo', $event->getRendered());
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
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame('testValue', $event->getValue());
        self::assertNull($event->getRendered());
    }

    public static function arrayReadableSingleDataProvider(): array
    {
        return [
            [[3, 2, 1], '3, 2, 1'],
            [[3 => ['foo', 'bar'], 2, 1], 'foo (bar), 2, 1']
        ];
    }

    #[Dataprovider('arrayReadableSingleDataProvider')]
    public function testRenderArrayReadableSingle($values, $excepted)
    {
        $event = $this->setupRenderReadablePropertyValueEvent($values, 'testProperty');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($values, $event->getValue());
        self::assertSame($excepted, $event->getRendered());
    }

    public static function timestampReadableForDateDataProvider(): array
    {
        $date = new DateTime();

        return [
            ['non-format', $date->getTimestamp(), $date->getTimestamp()],
            ['datim', $date->getTimestamp(), $date->format('Y-m-d H:i')],
            ['date', $date->getTimestamp(), $date->format('Y-m-d')],
            ['time', $date->getTimestamp(), $date->format('H:i')]
        ];
    }

    #[Dataprovider('timestampReadableForDateDataProvider')]
    public function testRenderTimestampReadableForDate($format, $time, $excepted): void
    {
        $this->runTimestampReadable('testProperty', $time, $excepted, ['rgxp' => $format]);
    }

    public function testRenderPropertyTimestampReadable(): void
    {
        $date = new DateTime();
        $this->runTimestampReadable('tstamp', $date->getTimestamp(), $date->format('H:i'));
    }

    public function testRenderValueIsDateTimeReadable(): void
    {
        $dateTime = new DateTime();

        $event = $this->setupRenderReadablePropertyValueEvent($dateTime, 'testProperty');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener(true);

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);
        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($dateTime, $event->getValue());
        self::assertSame($dateTime->format('Y-m-d H:i'), $event->getRendered());
    }

    public static function widgetCheckBoxReadableDataProvider(): array
    {
        return [
            [true, 'yes'],
            [false, 'no']
        ];
    }

    #[Dataprovider('widgetCheckBoxReadableDataProvider')]
    public function testRenderWidgetCheckBoxReadable($value, $excepted): void
    {
        $event = $this->setupRenderReadablePropertyValueEvent($value, 'testProperty');
        $event->getProperty()->setWidgetType('checkbox');

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($value, $event->getValue());
        self::assertSame($excepted, $event->getRendered());
    }

    public static function widgetTextAreaReadableDataProvider(): array
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

    #[Dataprovider('widgetTextAreaReadableDataProvider')]
    public function testRenderWidgetTextAreaReadable(
        $value,
        $propertyName,
        $propertyExtra,
        $widgetType,
        $excepted
    ): void {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName, $propertyExtra);
        $event->getProperty()->setWidgetType($widgetType);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        if (null === $excepted) {
            self::assertSame($value, $event->getValue());
            self::assertNull($event->getRendered());

            return;
        }

        self::assertSame($value, $event->getValue());
        self::assertSame($excepted, $event->getRendered());
    }

    public static function referenceReadableDataProvider(): array
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

    #[Dataprovider('referenceReadableDataProvider')]
    public function testRenderReferenceReadable($value, $propertyName, $propertyExtra, $excepted): void
    {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName, $propertyExtra);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher        = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener($event::NAME, [new Subscriber($scopeDeterminator), 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($value, $event->getValue());

        if (null === $excepted) {
            self::assertNull($event->getRendered());

            return;
        }

        self::assertSame($excepted, $event->getRendered());
    }

    public static function optionValueReadableDataProvider(): array
    {
        return [
            ['testValue', 'testProperty', null],
            ['testValue', 'testProperty', 'renderedTestValue', ['testValue' => 'renderedTestValue']],
            ['testValue', 'testProperty', 'renderedTestValue', ['testValue' => 'renderedTestValue'], true],
        ];
    }

    #[Dataprovider('optionValueReadableDataProvider')]
    public function testOptionValueReadable(
        $value,
        $propertyName,
        $excepted,
        array $propertyOptions = [],
        $optionsForListener = false
    ): void {
        $event = $this->setupRenderReadablePropertyValueEvent($value, $propertyName);
        if (!empty($propertyOptions) && !$optionsForListener) {
            $event->getProperty()->setOptions($propertyOptions);
        }

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener(false);

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);

        if (!empty($propertyOptions) && $optionsForListener) {
            $optionsListener = $this->mockGetPropertyOptionsEventListener($propertyOptions);
            $dispatcher->addListener(GetPropertyOptionsEvent::NAME, [$optionsListener, 'handle']);
        }

        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($value, $event->getValue());

        if (null === $excepted) {
            self::assertNull($event->getRendered());
            return;
        }

        self::assertSame($excepted, $event->getRendered());
    }

    public function testInitTwig(): void
    {
        self::markTestSkipped('The Contao twig extention we not using at time. We show for replace this.');

        $dispatcher = new EventDispatcher();

        $contaoTwig = ContaoTwig::getInstance();

        $event = new ContaoTwigInitializeEvent($contaoTwig);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener('contao-twig.init', [new Subscriber($scopeDeterminator), 'initTwig']);
        $dispatcher->dispatch($event, 'contao-twig.init');

        $environment = $contaoTwig->getEnvironment();

        self::assertTrue($environment->hasExtension('dc-general'));
        self::assertInstanceOf(DcGeneralExtension::class, $environment->getExtension('dc-general'));
    }

    public static function initializePanelsDataProvider(): array
    {
        $treeConstructorArgs = fn(SubscriberTest $test) => [
            $test->mockScopeDeterminator(),
            $test->getMockBuilder(CsrfTokenManagerInterface::class)->getMock(),
            'csrf-token-name'
        ];

        return [
            ['select', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['select', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [1, 2]],
            ['select', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [1, 2]],
            ['select', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [1, 2]],
            ['select', TreeView::class, $treeConstructorArgs, [1, 2]],

            ['copy', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['copy', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['copy', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['copy', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['copy', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['create', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['create', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['create', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['create', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['create', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['paste', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['paste', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['paste', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['paste', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['paste', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['delete', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['delete', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['delete', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['delete', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['delete', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['move', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['move', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['move', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['move', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['move', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['undo', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['undo', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['undo', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['undo', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['undo', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['edit', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['edit', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['edit', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['edit', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['edit', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['toggle', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['toggle', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['toggle', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['toggle', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['toggle', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['showAll', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['showAll', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['showAll', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['showAll', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['showAll', TreeView::class, $treeConstructorArgs, [3, 4]],

            ['show', NonBaseView::class, fn(SubscriberTest $test) => [], [1, 2]],
            ['show', BaseView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['show', ListView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['show', ParentView::class, fn(SubscriberTest $test) => [$test->mockScopeDeterminator()], [3, 4]],
            ['show', TreeView::class, $treeConstructorArgs, [3, 4]],
        ];
    }

    #[Dataprovider('initializePanelsDataProvider')]
    public function testInitializePanels(
        string $actionName,
        string $viewClass,
        callable $constructor,
        array $excepted
    ): void {
        $dispatcher = new EventDispatcher();

        $action      = new Action($actionName);
        $environment = new DefaultEnvironment();
        $event       = new ActionEvent($environment, $action);

        $dataDefinition = new DefaultContainer('foo');
        $environment->setDataDefinition($dataDefinition);

        $baseConfigRegistry = $this
            ->getMockBuilder(BaseConfigRegistry::class)
            ->onlyMethods(['getBaseConfig'])
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
            ->setConstructorArgs($constructor($this))
            ->onlyMethods(['getPanel'])
            ->getMock();
        $environment->setView($view);

        $panel = $this->getMockBuilder(DefaultPanelContainer::class)->onlyMethods(['initialize'])->getMock();
        $view
            ->method('getPanel')
            ->willReturn($panel);

        $panel
            ->method('initialize')
            ->willReturnCallback(
                static function ($config) {
                    $config->setSorting([3, 4]);
                }
            );

        $scopeDeterminator = $this->mockScopeDeterminator();
        $dispatcher->addListener(DcGeneralEvents::ACTION, [new Subscriber($scopeDeterminator), 'initializePanels']);
        $dispatcher->dispatch($event, DcGeneralEvents::ACTION);

        self::assertSame($dataConfig->getSorting(), $excepted);
    }

    public function testGetConfig(): void
    {
        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);

        self::assertInstanceOf(Config::class, Subscriber::getConfig());
        self::assertInstanceOf(Config::class, $subscriber::getConfig());
    }

    public function testSetConfig(): void
    {
        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);

        $subscriber::setConfig(Config::getInstance());

        self::assertInstanceOf(Config::class, $subscriber::getConfig());
    }

    private function runTimestampReadable($propertyName, $time, $excepted, array $extra = []): void
    {
        $event = $this->setupRenderReadablePropertyValueEvent($time, $propertyName, $extra);

        $scopeDeterminator = $this->mockScopeDeterminator();
        $subscriber        = new Subscriber($scopeDeterminator);
        $parseDateListener = $this->mockParseDateEventListener(
            !(isset($extra['rgxp']) && $extra['rgxp'] === 'non-format')
        );

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $dispatcher->addListener(ContaoEvents::DATE_PARSE, [$parseDateListener, 'handle']);
        $dispatcher->addListener($event::NAME, [$subscriber, 'renderReadablePropertyValue']);
        $dispatcher->dispatch($event, $event::NAME);

        self::assertSame($time, $event->getValue());

        if (isset($extra['rgxp']) && $extra['rgxp'] === 'non-format') {
            self::assertNull($event->getRendered());

            return;
        }

        self::assertSame($excepted, $event->getRendered());
    }

    private function setupRenderReadablePropertyValueEvent(
        $value,
        $propertyName,
        array $extra = []
    ): RenderReadablePropertyValueEvent {
        $defaultExtra = [
            'multiple' => null
        ];

        $property = new DefaultProperty($propertyName);
        $property->setExtra(array_merge($defaultExtra, $extra));

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

    private function mockParseDateEventListener(bool $expectsInvocation): object
    {
        return new class ($expectsInvocation) {
            public function __construct(
                private readonly bool $expectsInvocation
            ) {
            }

            public function handle($event, $eventName, $dispatcher): void
            {
                if (!$this->expectsInvocation) {
                    SubscriberTest::fail('Was not expected to be called!');
                }
                SubscriberTest::assertInstanceOf(ParseDateEvent::class, $event);
                SubscriberTest::assertSame(ContaoEvents::DATE_PARSE, $eventName);
                SubscriberTest::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);
                $event->setResult(Date::parse($event->getFormat(), $event->getTimestamp()));
            }
        };
    }

    private function mockGetPropertyOptionsEventListener($options): object
    {
        $listener = new class ($options)
        {
            public function __construct(private readonly array $options)
            {
            }

            public function handle($event, $eventName, $dispatcher): void
            {
                SubscriberTest::assertInstanceOf(GetPropertyOptionsEvent::class, $event);
                SubscriberTest::assertSame(GetPropertyOptionsEvent::NAME, $eventName);
                SubscriberTest::assertInstanceOf(EventDispatcher::class, $dispatcher);
                if ([] === $this->options) {
                    return;
                }
                $event->setOptions($this->options);
            }
        };

        return $listener;
    }

    private function mockScopeDeterminator(): RequestScopeDeterminator&MockObject
    {
        $scopeDeterminator = $this
            ->getMockBuilder(RequestScopeDeterminator::class)
            ->onlyMethods(['currentScopeIsBackend'])
            ->disableOriginalConstructor()
            ->getMock();

        $scopeDeterminator
            ->method('currentScopeIsBackend')
            ->willReturn(true);

        return $scopeDeterminator;
    }
}
