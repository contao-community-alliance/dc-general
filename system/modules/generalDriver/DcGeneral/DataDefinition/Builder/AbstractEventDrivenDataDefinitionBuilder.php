<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Builder;

use DcGeneral\Factory\Event\BuildDataDefinitionEvent;

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
	 * Creates an instance of itself and processes the event.
	 *
	 * The attached data definition {@link DcGeneral\DataDefinition\ContainerInterface}
	 * will be populated with the information from the builder's data source.
	 *
	 * @param BuildDataDefinitionEvent $event The event to process.
	 *
	 * @return void
	 */
	public static function process(BuildDataDefinitionEvent $event)
	{
		$builder = new static();
		/** @var DataDefinitionBuilderInterface $builder */
		$builder->build($event->getContainer(), $event);
	}
}
