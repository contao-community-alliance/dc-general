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
     * @var string
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
     * @param string $response The response.
     *
     * @return ActionEvent
     */
    public function setResponse($response)
    {
        $this->response = $response !== null ? (string) $response : null;
        return $this;
    }

    /**
     * Return the action response.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
