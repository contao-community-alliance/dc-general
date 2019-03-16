<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\ContainerAwareInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract base class for container aware events.
 *
 * This class solely implements the ContainerAwareInterface.
 */
abstract class AbstractContainerAwareEvent extends Event implements ContainerAwareInterface
{
    /**
     * The container in use.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Create a new container aware event.
     *
     * @param ContainerInterface $container The container in use.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }
}
