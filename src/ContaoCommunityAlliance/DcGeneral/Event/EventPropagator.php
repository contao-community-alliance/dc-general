<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The generic event propagator implementation.
 *
 * @package DcGeneral\Event
 */
class EventPropagator implements EventPropagatorInterface
{
    /**
     * The attached event dispatcher.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Create a new instance.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to attach.
     */
    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function propagate($eventName, $event = null, $suffixes = array())
    {
        if (!is_array($suffixes))
        {
            $suffixes = func_get_args();

            // Skip $eventName.
            array_shift($suffixes);
            // Skip $event.
            array_shift($suffixes);
        }

        while ($suffixes)
        {
            // First, try to dispatch to all DCA registered subscribers.
            $event = $this->propagateExact($eventName, $event, $suffixes);
            array_pop($suffixes);

            if ($event->isPropagationStopped() === true)
            {
                return $event;
            }
        }

        // Second, try to dispatch to all globally registered subscribers.
        if ((!$event) || $event->isPropagationStopped() === false)
        {
            $event = $this->dispatch($eventName, $event);
        }

        return $event;
    }

    /**
     * {@inheritDoc}
     */
    public function propagateExact($eventName, $event = null, $suffixes = array())
    {
        if (!is_array($suffixes))
        {
            $suffixes = func_get_args();

            // Skip $eventName.
            array_shift($suffixes);
            // Skip $event.
            array_shift($suffixes);
        }

        $event = $this->dispatch(
            sprintf(
                '%s%s',
                $eventName,
                '[' . implode('][', $suffixes) . ']'
            ),
            $event
        );

        return $event;
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     * @param Event  $event     The event to pass to the event handlers/listeners.
     *                          If not supplied, an empty Event instance is created.
     *
     * @return Event
     *
     * @api
     */
    public function dispatch($eventName, Event $event = null)
    {
        return $this->dispatcher->dispatch($eventName, $event);
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on.
     *
     * @param callable $listener  The listener.
     *
     * @param integer  $priority  The higher this value, the earlier an event listener will be triggered in the chain
     *                            (defaults to 0).
     *
     * @api
     *
     * @return void
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events he is
     * interested in and added as a listener for these events.
     *
     * @param EventSubscriberInterface $subscriber The subscriber.
     *
     * @api
     *
     * @return void
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|array $eventName The event(s) to remove a listener from.
     *
     * @param callable     $listener  The listener to remove.
     *
     * @return void
     */
    public function removeListener($eventName, $listener)
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * Removes an event subscriber.
     *
     * @param EventSubscriberInterface $subscriber The subscriber.
     *
     * @return void
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->removeSubscriber($subscriber);
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event.
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     */
    public function getListeners($eventName = null)
    {
        return $this->dispatcher->getListeners($eventName);
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event.
     *
     * @return Boolean true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
}
