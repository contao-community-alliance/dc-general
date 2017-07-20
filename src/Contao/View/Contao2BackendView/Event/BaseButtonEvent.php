<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class BaseButtonEvent.
 *
 * This event is the base for all button events for the Contao 2 backend view.
 */
class BaseButtonEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The name of the event.
     */
    const NAME = 'dc-general.view.contao2backend.button';

    /**
     * The html attributes to use for the button.
     *
     * @var string
     */
    protected $attributes;

    /**
     * The Html code to use for this button.
     *
     * @var string
     */
    protected $html;

    /**
     * The key/name of the button.
     *
     * @var string
     */
    protected $key;

    /**
     * The label to use for the button.
     *
     * @var string
     */
    protected $label;

    /**
     * The title to use for the button.
     *
     * @var string
     */
    protected $title;

    /**
     * Set the HTML attributes for the button.
     *
     * This might be a string like: 'onclick="foo" style="float:left;"' etc.
     *
     * @param string $attributes The attributes to be used.
     *
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the HTML attributes for the button.
     *
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the HTML code for the button.
     *
     * @param string $html The HTML code.
     *
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get the HTML code for the button.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set the key/name for the button.
     *
     * @param string $key The key/name to use.
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the action key/name for the button.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the button label text.
     *
     * @param string $label The label text to use.
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the button label text.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the button title.
     *
     * @param string $title The title text.
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the button title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
