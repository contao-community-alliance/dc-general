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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;

/**
 * This class simply mocks a session storage.
 */
class MockedSessionStorage implements SessionStorageInterface
{
    /**
     * The values.
     *
     * @var array
     */
    private $values;

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name.
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->values[$name]);
    }

    /**
     * Returns an attribute.
     *
     * @param string $name The attribute name.
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->values[$name];
    }

    /**
     * Sets an attribute.
     *
     * @param string $name  The attribute name.
     *
     * @param mixed  $value The attribute value.
     *
     * @return MockedSessionStorage
     */
    public function set($name, $value)
    {
        $this->values[$name] = $value;

        return $this;
    }

    /**
     * Returns all attributes.
     *
     * @return array
     */
    public function all()
    {
        return $this->values;
    }

    /**
     * Sets attributes.
     *
     * @param array $attributes Array of attributes.
     *
     * @return MockedSessionStorage
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->values[$name] = $value;
        }

        return $this;
    }

    /**
     * Removes an attribute.
     *
     * @param string $name The attribute name.
     *
     * @return MockedSessionStorage
     */
    public function remove($name)
    {
        unset($this->values[$name]);

        return $this;
    }

    /**
     * Clears all attributes.
     *
     * @return MockedSessionStorage
     */
    public function clear()
    {
        $this->values = array();

        return $this;
    }
}
