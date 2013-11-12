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

namespace DcGeneral\Event;

interface EventPropagatorInterface
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
	 * @param \Symfony\Component\EventDispatcher\Event $event   The Event to propagate.
	 *
	 * @param string[]                                $suffixes Suffixes to attach to the event.
	 *
	 * @return void
	 */
	public function propagate($event, $suffixes = array());
}
