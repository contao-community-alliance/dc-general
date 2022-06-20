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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\Cache\Http\InvalidateCacheTagsInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryService;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryServiceInterface;

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
     * The dc general factory.
     *
     * @var DcGeneralFactoryService
     */
    protected $factory;

    /**
     * The constructor.
     *
     * @param InvalidateCacheTagsInterface     $service The invalid http cache tags service.
     * @param DcGeneralFactoryServiceInterface $factory The dc general factory.
     */
    public function __construct(InvalidateCacheTagsInterface $service, DcGeneralFactoryServiceInterface $factory)
    {
        $this->service = $service;
        $this->factory = $factory;
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
        $this->service->purgeCacheTags($event->getModel(), $this->getEnvironment($event));
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
