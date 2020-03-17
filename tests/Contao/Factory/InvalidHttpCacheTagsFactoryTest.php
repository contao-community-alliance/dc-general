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

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\Factory;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Factory\InvalidHttpCacheTagsFactory;
use FOS\HttpCache\CacheInvalidator;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\Contao\Factory\InvalidHttpCacheTagsFactory
 */
class InvalidHttpCacheTagsFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $dispatcher   = $this->createMock(EventDispatcherInterface::class);
        $cacheManager = $this->createMock(CacheInvalidator::class);

        $factory = new InvalidHttpCacheTagsFactory($dispatcher, $cacheManager);

        self::assertInstanceOf(InvalidCacheTagsInterface::class, $factory->createService());
    }
}
