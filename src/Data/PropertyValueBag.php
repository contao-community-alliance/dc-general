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

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * A generic bag containing properties and their values.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 */
class PropertyValueBag implements PropertyValueBagInterface
{

    /**
     * All properties and its values in this bag.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * All properties that are marked as invalid and their error messages.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Create a new instance of a property bag.
     *
     * @param array|null $properties The initial property values to use.
     *
     * @throws DcGeneralInvalidArgumentException If the passed properties aren't null or an array.
     */
    public function __construct($properties = null)
    {
        if (\is_array($properties) || $properties instanceof \Traversable) {
            foreach ($properties as $property => $value) {
                $this->setPropertyValue($property, $value);
            }
        } elseif ($properties !== null) {
            throw new DcGeneralInvalidArgumentException(
                'The parameter $properties does not contain any properties nor values'
            );
        }
    }

    /**
     * Check if a property exists, otherwise through an exception.
     *
     * @param string $property The name of the property to require.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException If the property is not registered.
     *
     * @internal
     */
    protected function requirePropertyValue($property)
    {
        if (!$this->hasPropertyValue($property)) {
            throw new DcGeneralInvalidArgumentException('The property ' . $property . ' does not exists');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasPropertyValue($property)
    {
        return \array_key_exists($property, $this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyValue($property)
    {
        $this->requirePropertyValue($property);
        return $this->properties[$property];
    }

    /**
     * {@inheritdoc}
     */
    public function setPropertyValue($property, $value)
    {
        $this->properties[$property] = $value;

        $this->resetPropertyValueErrors($property);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePropertyValue($property)
    {
        $this->requirePropertyValue($property);
        unset($this->properties[$property]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInvalidPropertyValues()
    {
        return (bool) $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function hasNoInvalidPropertyValues()
    {
        return !$this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropertyValueInvalid($property)
    {
        $this->requirePropertyValue($property);
        return isset($this->errors[$property]) && (bool) $this->errors[$property];
    }

    /**
     * {@inheritdoc}
     */
    public function isPropertyValueValid($property)
    {
        $this->requirePropertyValue($property);
        return !$this->errors[$property];
    }

    /**
     * {@inheritdoc}
     */
    public function markPropertyValueAsInvalid($property, $error, $append = true)
    {
        $this->requirePropertyValue($property);

        if (!$append || !isset($this->errors[$property])) {
            $this->errors[$property] = [];
        }

        foreach ((array) $error as $singleError) {
            $this->errors[$property][] = $singleError;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPropertyValueErrors($property)
    {
        $this->requirePropertyValue($property);
        unset($this->errors[$property]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidPropertyNames()
    {
        return \array_keys($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyValueErrors($property)
    {
        $this->requirePropertyValue($property);
        return (array) $this->errors[$property];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidPropertyErrors()
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->hasPropertyValue($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getPropertyValue($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->setPropertyValue($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->removePropertyValue($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return $this->hasPropertyValue($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->getPropertyValue($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->setPropertyValue($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($name)
    {
        $this->removePropertyValue($name);
    }

    /**
     * Exports the {@link PropertyValueBag} to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->properties;
    }
}
