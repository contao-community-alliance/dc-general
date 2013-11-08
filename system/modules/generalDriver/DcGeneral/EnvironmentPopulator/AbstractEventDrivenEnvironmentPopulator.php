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

namespace DcGeneral\EnvironmentPopulator;

use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

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
	 * The attached environment {@link DcGeneral\EnvironmentInterface} will be populated
	 * with the information from the builder's data source.
	 *
	 * @param PopulateEnvironmentEvent $event The event to process
	 *
	 * @return void
	 */
	static public function process(PopulateEnvironmentEvent $event)
	{
		$builder = new static();
		/** @var $builder EnvironmentPopulatorInterface */
		$builder->populate($event->getEnvironment());
	}
}
