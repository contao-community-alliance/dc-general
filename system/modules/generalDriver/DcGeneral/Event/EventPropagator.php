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

class EventPropagator implements EventPropagatorInterface
{
	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
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
		if (!is_array($suffixes)) {
			$suffixes = func_get_args();

			// skip $event
			array_shift($suffixes);
		}

		$eventName = $event::NAME;

		while ($suffixes)
		{
			// First, try to dispatch to all DCA registered subscribers.
			$this->dispatcher->dispatch(
				sprintf(
					'%s%s',
					$eventName,
					'[' . implode('][', $suffixes) . ']'
				),
				$event
			);
			array_pop($suffixes);

			if ($event->isPropagationStopped() === true)
			{
				return;
			}
		}

		// Second, try to dispatch to all globally registered subscribers.
		if ($event->isPropagationStopped() !== true)
		{
			$this->dispatcher->dispatch($eventName, $event);
		}
	}
}
