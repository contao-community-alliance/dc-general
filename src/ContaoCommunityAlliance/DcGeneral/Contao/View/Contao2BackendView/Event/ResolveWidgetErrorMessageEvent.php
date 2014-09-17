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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class ResolveWidgetErrorMessageEvent.
 *
 * This event gets emitted when the error message of a widget shall get resolved.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class ResolveWidgetErrorMessageEvent extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.widget.resolve-error-message';

    /**
     * The error message.
     *
     * @var mixed
     */
    protected $error;

    /**
     * Create a new instance of the event.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param string               $error       The error message.
     */
    public function __construct(EnvironmentInterface $environment, $error)
    {
        parent::__construct($environment);
        $this->error = $error;
    }

    /**
     * Set the error message.
     *
     * @param mixed $error The error message.
     *
     * @return ResolveWidgetErrorMessageEvent
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Retrieve the error message.
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}
