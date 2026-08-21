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

namespace ContaoCommunityAlliance\DcGeneral\Test\Exception;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Exception\AbstractDefinitionAccessDeniedException;
use ContaoCommunityAlliance\DcGeneral\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotEditableException;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * These four exceptions used to extend DcGeneralRuntimeException and reach Contao's error screen
 * as an uncaught 500 with an English developer sentence - see
 * contao-community-alliance/dc-general#543. They now extend Symfony's security AccessDeniedException,
 * which the security firewall turns into a 403 (or a login redirect for an anonymous visitor)
 * before Contao ever renders an error page.
 */
#[CoversClass(AbstractDefinitionAccessDeniedException::class)]
#[CoversClass(NotDeletableException::class)]
#[CoversClass(NotCreatableException::class)]
#[CoversClass(NotEditableException::class)]
#[CoversClass(EditOnlyModeException::class)]
final class DefinitionAccessDeniedExceptionsTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        // Do not leak a container into other test classes sharing this process.
        $property = (new ReflectionClass(System::class))->getProperty('objContainer');
        $property->setValue(null, null);

        parent::tearDown();
    }

    public static function classesProvider(): array
    {
        return [
            'NotDeletableException' => [
                NotDeletableException::class,
                'exception.not_deletable',
                'This record cannot be deleted.',
            ],
            'NotCreatableException' => [
                NotCreatableException::class,
                'exception.not_creatable',
                'New records cannot be created here.',
            ],
            'NotEditableException'  => [
                NotEditableException::class,
                'exception.not_editable',
                'This record cannot be edited.',
            ],
            'EditOnlyModeException' => [
                EditOnlyModeException::class,
                'exception.edit_only_mode',
                'This view only supports editing existing records.',
            ],
        ];
    }

    /**
     * Without a Contao container (e.g. a plain unit test), the exception must not fatal - it
     * falls back to its hardcoded message.
     */
    #[DataProvider('classesProvider')]
    public function testFallsBackToTheHardcodedMessageWithoutAContainer(
        string $exceptionClass,
        string $translationKey,
        string $fallbackMessage
    ): void {
        $property = (new ReflectionClass(System::class))->getProperty('objContainer');
        $property->setValue(null, null);

        $exception = new $exceptionClass('tl_metamodel_item');

        self::assertSame($fallbackMessage, $exception->getMessage());
    }

    /**
     * A container without a translator service behaves exactly like no container at all.
     */
    #[DataProvider('classesProvider')]
    public function testFallsBackToTheHardcodedMessageWithoutATranslatorService(
        string $exceptionClass,
        string $translationKey,
        string $fallbackMessage
    ): void {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->method('has')->with('translator')->willReturn(false);
        System::setContainer($container);

        $exception = new $exceptionClass('tl_metamodel_item');

        self::assertSame($fallbackMessage, $exception->getMessage());
    }

    /**
     * With a translator available, the translated message is used and the definition name is
     * not appended - editors gain nothing from seeing a raw table name.
     */
    #[DataProvider('classesProvider')]
    public function testUsesTheTranslatedMessageWhenATranslatorIsAvailable(
        string $exceptionClass,
        string $translationKey,
        string $fallbackMessage
    ): void {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator
            ->method('trans')
            ->with($translationKey, [], 'dc-general')
            ->willReturn('Übersetzt: ' . $translationKey);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->method('has')->with('translator')->willReturn(true);
        $container->method('get')->with('translator')->willReturn($translator);
        System::setContainer($container);

        $exception = new $exceptionClass('tl_metamodel_item');

        self::assertSame('Übersetzt: ' . $translationKey, $exception->getMessage());
        self::assertStringNotContainsString('tl_metamodel_item', $exception->getMessage());
    }

    /**
     * If the catalogue has no entry, Symfony's translator returns the key itself - that must not
     * leak to the editor as the "message".
     */
    #[DataProvider('classesProvider')]
    public function testFallsBackWhenTheTranslatorReturnsTheKeyUnchanged(
        string $exceptionClass,
        string $translationKey,
        string $fallbackMessage
    ): void {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator->method('trans')->with($translationKey, [], 'dc-general')->willReturnArgument(0);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->method('has')->with('translator')->willReturn(true);
        $container->method('get')->with('translator')->willReturn($translator);
        System::setContainer($container);

        $exception = new $exceptionClass('tl_metamodel_item');

        self::assertSame($fallbackMessage, $exception->getMessage());
    }

    /**
     * The whole point of the change: Symfony's security firewall recognises this type and turns
     * it into a 403 (or a login redirect) instead of dc-general's exceptions bubbling up as an
     * uncaught 500.
     */
    #[DataProvider('classesProvider')]
    public function testIsASymfonySecurityAccessDeniedException(
        string $exceptionClass,
        string $translationKey,
        string $fallbackMessage
    ): void {
        $exception = new $exceptionClass('tl_metamodel_item');

        self::assertInstanceOf(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class, $exception);
    }
}
