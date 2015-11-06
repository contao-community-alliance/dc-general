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
