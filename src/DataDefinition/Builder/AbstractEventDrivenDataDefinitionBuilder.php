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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Builder;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractEventDrivenDataDefinitionBuilder.
 *
 * Abstract base class for an data definition builder.
 *
 * To use it, implement the method build() and register the class to the event dispatcher.
 *
 * @psalm-suppress MissingConstructor - properties will get set in process().
 */
abstract class AbstractEventDrivenDataDefinitionBuilder implements DataDefinitionBuilderInterface
{
    /**
     * Priority of the listener.
     * Just here for sanity, must be overwritten by implementation.
     *
     * @deprecated Should not be used at all.
     */
    public const PRIORITY = null;

    /**
     * The event dispatcher currently calling.
     *
     * @var EventDispatcherInterface|null
     */
    protected $dispatcher = null;

    /**
     * The name of the called event.
     *
     * @var string
     */
    protected $eventName = '';

    /**
     * Retrieve the dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getDispatcher()
    {
        if (null === $this->dispatcher) {
            throw new LogicException('Dispatcher not set.');
        }

        return $this->dispatcher;
    }

    /**
     * Retrieve the name of the dispatched event.
     *
     * @return string
     */
    protected function getDispatchedEventName()
    {
        return $this->eventName;
    }

    /**
     * Creates an instance of itself and processes the event.
     *
     * The attached data definition {@link ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface}
     * will be populated with the information from the builder's data source.
     *
     * @param BuildDataDefinitionEvent $event      The event to process.
     * @param string                   $eventName  The name of the event to process.
     * @param EventDispatcherInterface $dispatcher The event dispatcher calling us.
     *
     * @return void
     */
    public static function process(BuildDataDefinitionEvent $event, $eventName, $dispatcher)
    {
        /** @psalm-suppress UnsafeInstantiation */
        $builder             = new static();
        $builder->eventName  = $eventName;
        $builder->dispatcher = $dispatcher;

        /** @var DataDefinitionBuilderInterface $builder */
        $builder->build($event->getContainer(), $event);
    }
}
