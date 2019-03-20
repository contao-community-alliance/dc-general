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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

/**
 * Class GetGlobalButtonEvent.
 *
 * This event gets issued when the top level buttons in the listing view are being retrieved.
 *
 * These buttons include, but are not limited to, the "back" button and the "edit multiple" button.
 */
class GetGlobalButtonEvent extends BaseButtonEvent
{
    public const NAME = 'dc-general.view.contao2backend.get-global-button';

    /**
     * The hotkey for the button.
     *
     * @var string
     */
    protected $accessKey;

    /**
     * The css class to use.
     *
     * @var string
     */
    protected $class;

    /**
     * The href to use.
     *
     * @var string
     */
    protected $href;

    /**
     * Set the hotkey for the button.
     *
     * @param string $accessKey The hotkey for the button.
     *
     * @return $this
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * Get the hotkey for the button.
     *
     * @return string
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * Set the css class for this button.
     *
     * @param string $class The css class.
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the css class for this button.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the href for this button.
     *
     * @param string $href The href.
     *
     * @return $this
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get the href for this button.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }
}
