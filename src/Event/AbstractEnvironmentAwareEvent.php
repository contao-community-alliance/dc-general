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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract base class for environment aware events.
 */
abstract class AbstractEnvironmentAwareEvent extends Event implements EnvironmentAwareInterface
{
    /**
     * The environment attached to this event.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Create a new environment aware event.
     *
     * @param EnvironmentInterface $environment The environment to attach.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
