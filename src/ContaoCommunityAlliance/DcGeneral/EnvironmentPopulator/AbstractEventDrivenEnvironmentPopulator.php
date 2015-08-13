<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Abstract base implementation for an event driven environment populator.
 *
 * To utilize this class, you only have to implement the remaining method "populate" and register the populators
 * static method "process" to the event dispatcher.
 *
 * @package DcGeneral\EnvironmentPopulator
 */
abstract class AbstractEventDrivenEnvironmentPopulator implements EnvironmentPopulatorInterface
{
    /**
     * Priority of the listener.
     * Just here vor sanity, must be overwritten by implementation.
     */
    const PRIORITY = null;

    /**
     * Creates an instance of itself and processes the event.
     *
     * The attached environment {@link ContaoCommunityAlliance\DcGeneral\EnvironmentInterface} will be populated
     * with the information from the builder's data source.
     *
     * @param PopulateEnvironmentEvent $event The event to process.
     *
     * @return void
     */
    public static function process(PopulateEnvironmentEvent $event)
    {
        $builder = new static();
        /** @var $builder EnvironmentPopulatorInterface */
        $builder->populate($event->getEnvironment());
    }
}
