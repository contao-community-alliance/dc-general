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

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;

/**
 * {@inheritdoc}
 */
class SessionStorage implements SessionStorageInterface
{
    /**
     * The session key.
     *
     * @var string
     */
    private $key;

    /**
     * The attribute storage.
     *
     * @var array
     */
    private $attributes = null;

    /**
     * Create a new instance.
     *
     * @param string $key The key to use for storage.
     */
    public function __construct($key)
    {
        $this->key = (string) $key;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $this->load();
        return isset($this->attributes[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $this->load();
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->load();
        $this->attributes[$name] = $value;
        $this->persist();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $this->load();
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        $this->load();
        $this->attributes = array_merge($this->attributes, $attributes);
        $this->persist();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        $this->load();
        unset($this->attributes[$name]);
        $this->persist();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->load();
        $this->attributes = array();
        $this->persist();
    }

    /**
     * Load the data from the session if it has not been loaded yet.
     *
     * @return void
     */
    private function load()
    {
        if (null === $this->attributes) {
            $this->attributes = (array) \Session::getInstance()->get($this->key);
        }
    }

    /**
     * Save the data to the session.
     *
     * @return void
     */
    private function persist()
    {
        \Session::getInstance()->set($this->key, $this->attributes);
    }
}
