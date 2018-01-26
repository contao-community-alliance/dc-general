<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;

/**
 * This interface describes the data definition that holds all property information.
 */
interface PropertiesDefinitionInterface extends DefinitionInterface, \IteratorAggregate
{
    /**
     * The name of the definition.
     */
    const NAME = 'properties';

    /**
     * Get all properties.
     *
     * @return PropertyInterface[]|array
     */
    public function getProperties();

    /**
     * Get all property names.
     *
     * @return string[]|array
     */
    public function getPropertyNames();

    /**
     * Add a property information to the definition.
     *
     * @param PropertyInterface $property The property information to add.
     *
     * @return PropertiesDefinitionInterface
     */
    public function addProperty($property);

    /**
     * Remove a property information from the definition.
     *
     * @param PropertyInterface|string $property The information or the name of the property to remove.
     *
     * @return PropertiesDefinitionInterface
     */
    public function removeProperty($property);

    /**
     * Check if a property exists.
     *
     * @param string $name The name of the property.
     *
     * @return bool
     */
    public function hasProperty($name);

    /**
     * Get a property by name.
     *
     * @param string $name The name of the property.
     *
     * @return PropertyInterface
     */
    public function getProperty($name);
}
