<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\View\Contao2BackendView;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A fatal error caught while encoding a submitted value (a broken event listener, not a
 * validation constraint) used to simply vanish during an auto submit render pass: processInput()
 * and renderWidget() each build their own fresh Widget instance, and cleanErrors() wipes
 * whatever the render-time instance carries whenever the pass is an auto submit - see
 * contao-community-alliance/dc-general#100.
 */
#[CoversClass(ContaoWidgetManager::class)]
final class ContaoWidgetManagerFatalErrorsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Contao\Widget::__construct() reaches System::__construct(), which pulls in a real
        // Config singleton - alias both to the fixtures the rest of this suite already relies on
        // so a plain unit test can build a Widget without a booted framework.
        self::initializeContaoConfig();
        self::initializeContaoController();

        parent::setUpBeforeClass();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $property = (new ReflectionClass(System::class))->getProperty('objContainer');
        $property->setValue(null, null);

        parent::tearDown();
    }

    private function createManager(): ContaoWidgetManager
    {
        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap(
                [
                    ['contao.framework', $this->createStub(ContaoFramework::class)],
                    ['translator', $this->createStub(TranslatorInterface::class)],
                ]
            );
        System::setContainer($container);

        return new ContaoWidgetManager(
            $this->createStub(EnvironmentInterface::class),
            $this->createStub(ModelInterface::class)
        );
    }

    private function createWidget(): Widget
    {
        return new class () extends Widget {
            #[\Override]
            public function generate()
            {
                return '';
            }
        };
    }

    private function accessibleCleanErrors(ContaoWidgetManager $manager): \ReflectionMethod
    {
        $method = (new ReflectionClass($manager))->getMethod('cleanErrors');
        $method->setAccessible(true);

        return $method;
    }

    private function setFatalErrors(ContaoWidgetManager $manager, array $fatalErrors): void
    {
        $property = (new ReflectionClass($manager))->getProperty('fatalErrors');
        $property->setAccessible(true);
        $property->setValue($manager, $fatalErrors);
    }

    public function testAutoSubmitReAppliesARecordedFatalErrorAfterWiping(): void
    {
        $manager = $this->createManager();
        $this->setFatalErrors($manager, ['my_property' => ['Encoding blew up.']]);

        $widget = $this->createWidget();
        $widget->addError('a validation error that must not survive');

        $cleanErrors = $this->accessibleCleanErrors($manager);
        $cleanErrors->invoke($manager, $widget, true, 'my_property');

        self::assertTrue($widget->hasErrors());
        self::assertSame(['Encoding blew up.'], $widget->getErrors());
    }

    public function testAutoSubmitWithoutARecordedFatalErrorStaysErrorFree(): void
    {
        $manager = $this->createManager();

        $widget = $this->createWidget();
        $widget->addError('a validation error that must not survive');

        $cleanErrors = $this->accessibleCleanErrors($manager);
        $cleanErrors->invoke($manager, $widget, true, 'my_property');

        self::assertFalse($widget->hasErrors());
    }

    public function testRealSubmitLeavesTheWidgetUntouched(): void
    {
        $manager = $this->createManager();
        $this->setFatalErrors($manager, ['my_property' => ['Encoding blew up.']]);

        $widget = $this->createWidget();
        $widget->addError('a validation error from Widget::validate()');

        $cleanErrors = $this->accessibleCleanErrors($manager);
        $cleanErrors->invoke($manager, $widget, false, 'my_property');

        // Not an auto submit - cleanErrors() must not touch the widget at all, fatal error or not.
        self::assertSame(['a validation error from Widget::validate()'], $widget->getErrors());
    }

    public function testFatalErrorOfAnotherPropertyIsNotAppliedToThisWidget(): void
    {
        $manager = $this->createManager();
        $this->setFatalErrors($manager, ['other_property' => ['Encoding blew up.']]);

        $widget = $this->createWidget();

        $cleanErrors = $this->accessibleCleanErrors($manager);
        $cleanErrors->invoke($manager, $widget, true, 'my_property');

        self::assertFalse($widget->hasErrors());
    }
}
