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
			$event = $this->dispatcher->dispatch($eventName, $event);
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

		$event = $this->dispatcher->dispatch(
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
