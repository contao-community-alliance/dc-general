<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * The session storage.
 *
 * The session storage hold information's persistent in the session.
 */
interface SessionStorageInterface
{
    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name.
     *
     * @return bool
     */
    public function has($name);

    /**
     * Returns an attribute.
     *
     * @param string $name The attribute name.
     *
     * @return mixed
     */
    public function get($name);

    /**
     * Sets an attribute.
     *
     * @param string $name  The attribute name.
     *
     * @param mixed  $value The attribute value.
     *
     * @return static
     */
    public function set($name, $value);

    /**
     * Returns all attributes.
     *
     * @return array
     */
    public function all();

    /**
     * Sets attributes.
     *
     * @param array $attributes Array of attributes.
     *
     * @return static
     */
    public function replace(array $attributes);

    /**
     * Removes an attribute.
     *
     * @param string $name The attribute name.
     *
     * @return static
     */
    public function remove($name);

    /**
     * Clears all attributes.
     *
     * @return static
     */
    public function clear();
}
