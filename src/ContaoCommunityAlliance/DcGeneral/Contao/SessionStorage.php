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

    public function __construct($key)
    {
        $this->key = $key;
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

    private function load()
    {
        if (null === $this->attributes) {
            $this->attributes = (array) \Session::getInstance()->get($this->key);
        }
    }

    private function persist()
    {
        \Session::getInstance()->set($this->key, $this->attributes);
    }
}
