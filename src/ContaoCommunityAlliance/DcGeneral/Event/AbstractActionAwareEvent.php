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

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class is the base foundation for an action event.
 *
 * @package DcGeneral\Event
 */
abstract class AbstractActionAwareEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The action.
     *
     * @var Action
     */
    protected $action;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param Action               $action      The action.
     */
    public function __construct(EnvironmentInterface $environment, Action $action)
    {
        parent::__construct($environment);
        $this->action = $action;
    }

    /**
     * Return the action.
     *
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }
}
