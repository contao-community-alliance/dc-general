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

namespace ContaoCommunityAlliance\DcGeneral\Test\Event;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\Event\InvalidHttpCacheTagsEvent
 */
class InvalidHttpCacheTagsEventTest extends TestCase
{
    public function testEvent(): void
    {
        $environment = $this->createMock(EnvironmentInterface::class);
        $event       = new InvalidHttpCacheTagsEvent($environment);
        $event->setNamespace('namespace')->setTags(
            [
                'namespace.foo',
                'namespace.foo.1',
                'namespace.foo.1',
                'namespace.bar',
                'namespace.bar.1',
                'namespace.bar.1'
            ]
        );

        self::assertSame('namespace', $event->getNamespace());
        self::assertSame(
            [
                'namespace.foo',
                'namespace.foo.1',
                'namespace.foo.1',
                'namespace.bar',
                'namespace.bar.1',
                'namespace.bar.1'
            ],
            $event->getTags()
        );
    }

    public function testOverridingNamespaceForbidden(): void
    {
        $this->expectException(\RuntimeException::class);

        $environment = $this->createMock(EnvironmentInterface::class);
        $event       = new InvalidHttpCacheTagsEvent($environment);
        $event->setNamespace('namespace')->setNamespace('crash');
    }
}
