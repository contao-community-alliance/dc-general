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

        $this->assertFalse($definition->hasDefinition('foo'));
        try {
            $definition->getDefinition('foo');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(DcGeneralInvalidArgumentException::class, $exception);
            $this->assertSame('Data definition foo is not contained.', $exception->getMessage());
        }

        $this
            ->assertInstanceOf(DataDefinitionContainerInterface::class, $definition->setDefinition('foo', $container));
        $this->assertTrue($definition->hasDefinition('foo'));
        $this->assertSame($container, $definition->getDefinition('foo'));

        $this
            ->assertInstanceOf(DataDefinitionContainerInterface::class, $definition->setDefinition('foo', null));
        $this->assertFalse($definition->hasDefinition('foo'));
    }
}
