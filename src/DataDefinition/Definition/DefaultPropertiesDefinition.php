<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This is the reference implementation for PropertiesDefinitionInterface.
 */
class DefaultPropertiesDefinition implements PropertiesDefinitionInterface
{
    /**
     * The property definitions contained.
     *
     * @var PropertyInterface[]
     */
    protected $properties = [];

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyNames()
    {
        return \array_keys($this->properties);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When an invalid property has been passed or a property with the given
     *                                           name has already been registered.
     */
    public function addProperty($property)
    {
        if (!($property instanceof PropertyInterface)) {
            throw new DcGeneralInvalidArgumentException('Passed value is not an instance of PropertyInterface.');
        }

        $name = $property->getName();

        if ($this->hasProperty($name)) {
            throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is already registered.');
        }

        $this->properties[$name] = $property;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When an a property with the given name has not been registered.
     */
    public function removeProperty($property)
    {
        $name = ($property instanceof PropertyInterface) ? $property->getName() : $property;

        if (!$this->hasProperty($name)) {
            throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is not registered.');
        }

        unset($this->properties[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When a property with the given name has not been registered.
     */
    public function getProperty($name)
    {
        if (!$this->hasProperty($name)) {
            throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is not registered.');
        }

        return $this->properties[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->properties);
    }
}
