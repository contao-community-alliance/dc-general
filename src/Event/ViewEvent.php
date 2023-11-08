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

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event occurs when a view should rendered.
 */
class ViewEvent extends AbstractActionAwareEvent
{
    /**
     * The view name.
     *
     * @var string
     */
    protected $viewName;

    /**
     * The view context attributes.
     *
     * @var array
     */
    protected $context;

    /**
     * The action response, if any is set.
     *
     * @var string|null
     */
    protected $response;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param Action               $action      The action.
     * @param string               $viewName    The view name.
     * @param array                $context     The context attributes.
     */
    public function __construct(EnvironmentInterface $environment, Action $action, $viewName, array $context)
    {
        parent::__construct($environment, $action);
        /** @psalm-suppress RedundantCastGivenDocblockType - only redundant when strict typed */
        $this->viewName = (string) $viewName;
        $this->context  = $context;
    }

    /**
     * Returns the view name.
     *
     * @return string
     */
    public function getViewName()
    {
        return $this->viewName;
    }

    /**
     * Returns the view context attributes.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the view context attributes.
     *
     * @param array $context The context attributes.
     *
     * @return static
     */
    public function setContext(array $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Set the action response.
     *
     * @param string|null $response The response.
     *
     * @return ViewEvent
     */
    public function setResponse($response)
    {
        /** @psalm-suppress RedundantCastGivenDocblockType - only redundant when strict typed */
        $this->response = (null !== $response) ? (string) $response : null;
        return $this;
    }

    /**
     * Return the action response.
     *
     * @return string|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
