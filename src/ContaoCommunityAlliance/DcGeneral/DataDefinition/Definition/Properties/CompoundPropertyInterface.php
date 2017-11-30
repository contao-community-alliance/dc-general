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

/**
 * This interface extends the property interface and has information for sub property.
 */
interface CompoundPropertyInterface extends PropertyInterface
{
    /**
     * Remove all sub properties from this property information.
     *
     * @return CompoundPropertyInterface
     */
    public function clearProperties();

    /**
     * Get all sub properties in this property information.
     *
     * @return array|PropertyInterface[]
     */
    public function getProperties();

    /**
     * Set all sub properties to this property information.
     *
     * @param array|PropertyInterface $properties The collection properties.
     *
     * @return CompoundPropertyInterface
     */
    public function setProperties(array $properties);

    /**
     * Determine if a sub property with the given name exists in this property information.
     *
     * @param string $name The name of the collection property to search for.
     *
     * @return bool
     */
    public function hasProperty($name);

    /**
     * Get a sub property from this property information.
     *
     * @param string $name The collection property name.
     *
     * @return CompoundPropertyInterface
     */
    public function getProperty($name);

    /**
     * Add a sub property to this property information.
     *
     * @param PropertyInterface $property The collection property to add.
     *
     * @return CompoundPropertyInterface
     */
    public function addProperty(PropertyInterface $property);

    /**
     * Remove a sub property from this property information.
     *
     * @param PropertyInterface $property The collection property to remove.
     *
     * @return CompoundPropertyInterface
     */
    public function removeProperty(PropertyInterface $property);
}
