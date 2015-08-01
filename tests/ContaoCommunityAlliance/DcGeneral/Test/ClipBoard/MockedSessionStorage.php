<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
