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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

/**
 * Class GetPasteRootButtonEvent.
 *
 * This event gets emitted when a root button get's rendered in hierarchical mode.
 */
class GetPasteRootButtonEvent extends BaseButtonEvent
{
    const NAME = 'dc-general.view.contao2backend.get-paste-root-button';

    /**
     * The href information to use for the paste button.
     *
     * @var string
     */
    protected $href;

    /**
     * Determinator if the paste button shall be disabled.
     *
     * @var bool
     */
    protected $pasteDisabled;

    /**
     * Set the href for the button.
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
     * Get the href for the button.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set the determinator if the button shall be disabled or not.
     *
     * @param boolean $pasteDisabled The flag.
     *
     * @return $this
     */
    public function setPasteDisabled($pasteDisabled)
    {
        $this->pasteDisabled = $pasteDisabled;

        return $this;
    }

    /**
     * Check if the paste button shall be disabled or not.
     *
     * @return boolean
     */
    public function isPasteDisabled()
    {
        return $this->pasteDisabled;
    }
}
