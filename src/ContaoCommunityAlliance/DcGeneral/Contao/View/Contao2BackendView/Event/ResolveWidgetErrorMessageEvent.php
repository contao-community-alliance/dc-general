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
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class ResolveWidgetErrorMessageEvent.
 *
 * This event gets emitted when the error message of a widget shall get resolved.
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
