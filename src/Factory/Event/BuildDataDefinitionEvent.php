<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a data definition is being built.
 */
class BuildDataDefinitionEvent extends Event
{
    const NAME = 'dc-general.factory.build-data-definition';

    /**
     * The data definition container being built.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Create a new instance.
     *
     * @param ContainerInterface $container The container being built.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieve the data definition container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
