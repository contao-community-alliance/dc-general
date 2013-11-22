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

namespace DcGeneral\DataDefinition\Builder;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;

interface DataDefinitionBuilderInterface
{
	/**
	 * Build a data definition and store it into the environments container.
	 *
	 * @param ContainerInterface       $container
	 *
	 * @param BuildDataDefinitionEvent $event
	 *
	 * @return void
	 */
	public function build(ContainerInterface $container, BuildDataDefinitionEvent $event);
}
