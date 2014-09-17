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

use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract base class for environment aware events
 *
 * @package DcGeneral\Event
 */
abstract class AbstractEnvironmentAwareEvent
    extends Event
    implements EnvironmentAwareInterface
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
