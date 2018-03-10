<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
 */
interface DataDefinitionBuilderInterface
{
    /**
     * Build a data definition and store it into the environments container.
     *
     * @param ContainerInterface       $container The data definition container to populate.
     * @param BuildDataDefinitionEvent $event     The event that has been triggered.
     *
     * @return void
     */
    public function build(ContainerInterface $container, BuildDataDefinitionEvent $event);
}
