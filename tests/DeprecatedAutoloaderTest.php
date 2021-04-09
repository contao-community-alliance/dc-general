<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\ColorPickerWizardListener;
use ContaoCommunityAlliance\DcGeneral\Exception\DefinitionException;
use ContaoCommunityAlliance\DcGeneral\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotDeletableException;
use PHPUnit\Framework\TestCase;

/**
 * This class tests if the deprecated autoloader works.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Exception\DefinitionException
 * @covers \ContaoCommunityAlliance\DcGeneral\Exception\EditOnlyModeException
 * @covers \ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException
 * @covers \ContaoCommunityAlliance\DcGeneral\Exception\NotDeletableException
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\ColorPickerWizardListener
 * @covers \ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistryInterface
 * @covers \ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistry
 */
class DeprecatedAutoloaderTest extends TestCase
{
    /**
     * Provide the alias class map.
     *
     * @return \Generator
     */
    public function provideAliasClassMap(): \Generator
    {
        yield 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\DefinitionException' => [
            'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\DefinitionException',
            DefinitionException::class
        ];

        yield 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException' => [
             'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException',
             EditOnlyModeException::class
         ];

         yield 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException' => [
             'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException',
             NotCreatableException::class
         ];

         yield 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException' => [
             'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException',
             NotDeletableException::class
         ];

         yield 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\ColorPickerWizardSubscriber' => [
             'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\ColorPickerWizardSubscriber',
             ColorPickerWizardListener::class
         ];

         yield 'ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry' => [
             'ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry',
             BaseConfigRegistry::class
         ];
    }

    /**
     * Provide the alias interface map.
     *
     * @return \Generator
     */
    public function provideAliasInterfaceMap(): \Generator
    {
        yield 'ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface' => [
            'ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface',
            BaseConfigRegistryInterface::class
        ];
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldClass Old class name.
     * @param string $newClass New class name.
     *
     * @dataProvider provideAliasClassMap
     */
    public function testDeprecatedClassesAreAliased(string $oldClass, string $newClass): void
    {
        self::assertTrue(\class_exists($oldClass), \sprintf('Class select "%s" is not found.', $oldClass));

        $oldClassReflection = new \ReflectionClass($oldClass);
        $newClassReflection = new \ReflectionClass($newClass);

        self::assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldInterface Old interface name.
     * @param string $newInterface New interface name.
     *
     * @dataProvider provideAliasInterfaceMap
     */
    public function testDeprecatedInterfacesAreAliased(string $oldInterface, string $newInterface): void
    {
        self::assertTrue(
            \interface_exists($oldInterface),
            \sprintf('Interface select "%s" is not found.', $oldInterface)
        );

        $oldClassReflection = new \ReflectionClass($oldInterface);
        $newClassReflection = new \ReflectionClass($newInterface);

        self::assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }
}
