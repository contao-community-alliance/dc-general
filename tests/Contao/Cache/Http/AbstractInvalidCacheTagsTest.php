<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http\AbstractInvalidCacheTags;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http\AbstractInvalidCacheTags
 */
class AbstractInvalidCacheTagsTest extends TestCase
{
    public function testEventListener(): void
    {
        $environment = $this->createMock(EnvironmentInterface::class);

        $model = $this->createMock(ModelInterface::class);

        $event = $this->createMock(AbstractModelAwareEvent::class);
        $event
            ->expects(self::once())
            ->method('getEnvironment')
            ->willReturn($environment);
        $eventModelCalled = false;
        $event
            ->expects(self::once())
            ->method('getModel')
            ->willReturnCallback(
                function () use (&$eventModelCalled, $model) {
                    $eventModelCalled = true;
                    return $model;
                }
            );

        $service = $this->createMock(InvalidCacheTagsInterface::class);

        $listener = $this->getMockForAbstractClass(AbstractInvalidCacheTags::class, [$service]);
        $listener->__invoke($event);

        self::assertTrue($eventModelCalled);
    }
}
