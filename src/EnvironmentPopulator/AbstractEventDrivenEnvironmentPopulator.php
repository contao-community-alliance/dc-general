<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Abstract base implementation for an event driven environment populator.
 *
 * To utilize this class, you only have to implement the remaining method "populate" and register the populators
 * static method "process" to the event dispatcher.
 */
abstract class AbstractEventDrivenEnvironmentPopulator implements EnvironmentPopulatorInterface
{
    /**
     * Priority of the listener.
     * Just here vor sanity, must be overwritten by implementation.
     *
     * @var int
     */
    public const PRIORITY = 0;

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
        /** @psalm-suppress UnsafeInstantiation */
        $builder = new static();
        /** @var EnvironmentPopulatorInterface $builder */
        $builder->populate($event->getEnvironment());
    }
}
