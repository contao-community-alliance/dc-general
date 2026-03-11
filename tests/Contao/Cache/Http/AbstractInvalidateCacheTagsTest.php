<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidateCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http\AbstractInvalidateCacheTags;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryServiceInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(AbstractInvalidateCacheTags::class)]
final class AbstractInvalidateCacheTagsTest extends TestCase
{
    public function testEventListener(): void
    {
        $environment = $this->createMock(EnvironmentInterface::class);

        $model = $this->createMock(ModelInterface::class);

        $event = $this->createMock(AbstractModelAwareEvent::class);
        $event
            ->expects($this->once())
            ->method('getEnvironment')
            ->willReturn($environment);
        $eventModelCalled = false;
        $event
            ->expects($this->once())
            ->method('getModel')
            ->willReturnCallback(
                function () use (&$eventModelCalled, $model) {
                    $eventModelCalled = true;
                    return $model;
                }
            );

        $service = $this->createMock(InvalidateCacheTagsInterface::class);
        $factory = $this->createMock(DcGeneralFactoryServiceInterface::class);

        $listener = $this->getMockBuilder(AbstractInvalidateCacheTags::class)
            ->setConstructorArgs([$service, $factory])
            ->onlyMethods([])
            ->getMock();
        $listener->__invoke($event);

        self::assertTrue($eventModelCalled);
    }
}
