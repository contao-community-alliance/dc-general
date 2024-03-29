<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test for the data definition container.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinitionContainer
 */
class DataDefinitionContainerTest extends TestCase
{
    public function testSetterAndGetter()
    {
        $container  = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new DataDefinitionContainer();

        self::assertFalse($definition->hasDefinition('foo'));
        try {
            $definition->getDefinition('foo');
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralInvalidArgumentException::class, $exception);
            self::assertSame('Data definition foo is not contained.', $exception->getMessage());
        }

        self
            ::assertInstanceOf(DataDefinitionContainerInterface::class, $definition->setDefinition('foo', $container));
        self::assertTrue($definition->hasDefinition('foo'));
        self::assertSame($container, $definition->getDefinition('foo'));

        self
            ::assertInstanceOf(DataDefinitionContainerInterface::class, $definition->setDefinition('foo', null));
        self::assertFalse($definition->hasDefinition('foo'));
    }
}
