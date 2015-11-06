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

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class BaseGetButtonsEvent.
 *
 * Base event for retrieving buttons. This event is not being emitted anywhere as it is only a base class for other
 * events.
 */
class BaseGetButtonsEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The name of the event.
     */
    const NAME = 'dc-general.view.contao2backend.get-buttons';

    /**
     * The list of buttons.
     *
     * @var string[]
     */
    protected $buttons;

    /**
     * Set the list of buttons.
     *
     * @param string[] $buttons The buttons to be returned.
     *
     * @return $this
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * Get the list of buttons.
     *
     * @return string[]
     */
    public function getButtons()
    {
        return $this->buttons;
    }
}
