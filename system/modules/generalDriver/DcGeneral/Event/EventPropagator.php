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
	public function propagate($event, $suffixes = array())
	{
		if (!is_array($suffixes))
		{
			$suffixes = func_get_args();

			// Skip $event.
			array_shift($suffixes);
		}

		$eventName = $event::NAME;

		while ($suffixes)
		{
			// First, try to dispatch to all DCA registered subscribers.
			$this->propagateExact($event, $suffixes);
			array_pop($suffixes);

			if ($event->isPropagationStopped() === true)
			{
				return $event;
			}
		}

		// Second, try to dispatch to all globally registered subscribers.
		if ($event->isPropagationStopped() !== true)
		{
			$this->dispatcher->dispatch($eventName, $event);
		}

		return $event;
	}

	/**
	 * {@inheritDoc}
	 */
	public function propagateExact($event, $suffixes = array())
	{
		if (!is_array($suffixes))
		{
			$suffixes = func_get_args();

			// Skip $event.
			array_shift($suffixes);
		}

		$eventName = $event::NAME;

		$this->dispatcher->dispatch(
			sprintf(
				'%s%s',
				$eventName,
				'[' . implode('][', $suffixes) . ']'
			),
			$event
		);

		return $event;
	}
}
