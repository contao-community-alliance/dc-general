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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of a property with sub property definition.
 */
class DefaultCompoundProperty extends DefaultProperty implements CompoundPropertyInterface
{
    /**
     * The subProperties of internal sub property in this property.
     *
     * @var array|PropertyInterface[]
     */
    private $properties = array();

    /**
     * {@inheritDoc}
     */
    public function clearProperties()
    {
        $this->properties = array();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritDoc}
     */
    public function setProperties(array $properties)
    {
        $this->clearProperties();

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($name)
    {
        if (!$this->hasProperty($name)) {
            throw new DcGeneralInvalidArgumentException('Property ' . $name . ' is not registered.');
        }

        return $this->properties[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function addProperty(PropertyInterface $property)
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeProperty(PropertyInterface $property)
    {
        unset($this->properties[$property->getName()]);

        return $this;
    }
}
