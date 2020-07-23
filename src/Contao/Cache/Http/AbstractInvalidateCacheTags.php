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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * The abstract class for the invalidate http cache tags.
 */
abstract class AbstractInvalidateCacheTags
{
    /**
     * The invalid http cache tags service.
     *
     * @var ContaoInvalidateCacheTags
     */
    private $service;

    /**
     * The constructor.
     *
     * @param InvalidCacheTagsInterface $service The invalid http cache tags service.
     */
    public function __construct(InvalidCacheTagsInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Invoke the event.
     *
     * @param AbstractModelAwareEvent $event The event.
     *
     * @return void
     */
    public function __invoke(AbstractModelAwareEvent $event): void
    {
        $this->service
            ->setEnvironment($this->getEnvironment($event))
            ->purgeCacheTags($event->getModel());
    }

    /**
     * Get the environment.
     *
     * @param AbstractModelAwareEvent $event The event.
     *
     * @return EnvironmentInterface
     */
    protected function getEnvironment(AbstractModelAwareEvent $event): EnvironmentInterface
    {
        return $event->getEnvironment();
    }
}
