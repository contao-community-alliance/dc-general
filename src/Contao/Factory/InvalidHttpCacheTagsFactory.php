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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Factory;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http\ContaoInvalidCacheTags;
use FOS\HttpCache\CacheInvalidator;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * This factory is for create the invalid http cache tags service.
 */
class InvalidHttpCacheTagsFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The http cache manager.
     *
     * @var CacheInvalidator|null
     */
    private $cacheManager;

    /**
     * The constructor.
     *
     * @param EventDispatcherInterface $dispatcher   The event dispatcher.
     * @param CacheInvalidator|null    $cacheManager The http cache manager.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheInvalidator $cacheManager = null
    ) {
        $this->dispatcher   = $dispatcher;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Create new instance of the service.
     *
     * @return InvalidCacheTagsInterface
     */
    public function createService(): InvalidCacheTagsInterface
    {
        return new ContaoInvalidCacheTags('contao.db.', $this->dispatcher, $this->cacheManager);
    }
}
