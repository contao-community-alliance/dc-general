<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class is the base foundation for an action event.
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
