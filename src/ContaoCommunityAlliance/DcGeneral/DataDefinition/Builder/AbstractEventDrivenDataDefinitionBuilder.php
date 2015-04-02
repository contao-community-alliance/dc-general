<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Builder;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractEventDrivenDataDefinitionBuilder.
 *
 * Abstract base class for an data definition builder.
 *
 * To use it, implement the method build() and register the class to the event dispatcher.
 *
 * @package DcGeneral\DataDefinition\Builder
 */
abstract class AbstractEventDrivenDataDefinitionBuilder implements DataDefinitionBuilderInterface
{
    /**
     * Priority of the listener.
     * Just here for sanity, must be overwritten by implementation.
     */
    const PRIORITY = null;

    /**
     * The event dispatcher currently calling.
     *
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * The name of the called event.
     *
     * @var string
     */
    protected $eventName;

    /**
     * Retrieve the dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getDispatcher()
    {
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
     *
     * @param string                   $eventName  The name of the event to process.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher calling us.
     *
     * @return void
     */
    public static function process(BuildDataDefinitionEvent $event, $eventName, $dispatcher)
    {
        $builder             = new static();
        $builder->eventName  = $eventName;
        $builder->dispatcher = $dispatcher;

        /** @var DataDefinitionBuilderInterface $builder */
        $builder->build($event->getContainer(), $event);
    }
}
