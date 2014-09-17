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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Builder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * This interface describes a builder of a DataDefinition.
 *
 * Builders are used in the first pass of the instantiation of DcGeneral to populate the DataDefinition.
 * The builders should react to a BuildDataDefinitionEvent and therefore must be registered in the event dispatcher.
 *
 * @package DcGeneral\DataDefinition\Builder
 */
interface DataDefinitionBuilderInterface
{
    /**
     * Build a data definition and store it into the environments container.
     *
     * @param ContainerInterface       $container The data definition container to populate.
     *
     * @param BuildDataDefinitionEvent $event     The event that has been triggered.
     *
     * @return void
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event);
}
