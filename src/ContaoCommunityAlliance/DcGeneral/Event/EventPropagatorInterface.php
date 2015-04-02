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

namespace ContaoCommunityAlliance\DcGeneral\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generic event propagator.
 *
 * The event propagator is used to dispatch an event to the attached event dispatcher.
 *
 * When propagating an event, one can pass an array of suffixes that will get appended to the event name in a loop.
 *
 * @package DcGeneral\Event
 *
 * @deprecated Event propagation turned out to be not very effective. Use plain event dispatching and check in the
 *             listener if you want to handle the event.
 */
interface EventPropagatorInterface extends EventDispatcherInterface
{
    /**
     * Propagate an event to the defined event dispatcher.
     *
     * The given suffixes will get appended to the event name and the resulting event name will get fired.
     *
     * For each round of firing, the last element from the suffixes get's dropped and the event fired again.
     *
     * The loop stops as soon as the passed event has isPropagationStopped() === true
     *
     * Example:
     *   Eventname: dc-general.some.event
     *   Suffixes:  array('param1', 'param2')
     * Resulting Events:
     *   1. dc-general.some.event[param1][param2]
     *   2. dc-general.some.event[param1]
     *   3. dc-general.some.event
     *
     * @param string                                   $eventName The event name of the event to propagate.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event     The Event to propagate (optional).
     *
     * @param string[]                                 $suffixes  Suffixes to attach to the event.
     *
     * @return \Symfony\Component\EventDispatcher\Event
     *
     * @deprecated Event propagation turned out to be not very effective. Use plain event dispatching and check in the
     *             listener if you want to handle the event.
     */
    public function propagate($eventName, $event = null, $suffixes = array());

    /**
     * Propagate an event to the defined event dispatcher.
     *
     * The given suffixes will get appended to the event name and the resulting event name will get fired.
     *
     * @param string                                   $eventName The event name of the event to propagate.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event     The Event to propagate.
     *
     * @param string[]                                 $suffixes  Suffixes to attach to the event.
     *
     * @return \Symfony\Component\EventDispatcher\Event
     *
     * @deprecated Event propagation turned out to be not very effective. Use plain event dispatching and check in the
     *             listener if you want to handle the event.
     */
    public function propagateExact($eventName, $event, $suffixes = array());
}
