<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;

/**
 * This interface describes the data definition that holds all property information.
 *
 * @extends \IteratorAggregate<string, PropertyInterface>
 */
interface PropertiesDefinitionInterface extends DefinitionInterface, \IteratorAggregate
{
    /**
     * The name of the definition.
     */
    public const string NAME = 'properties';

    /**
     * Get all properties.
     *
     * @return array<string, PropertyInterface>
     */
    public function getProperties(): array;

    /**
     * Get all property names.
     *
     * @return list<string>
     */
    public function getPropertyNames(): array;

    /**
     * Add a property information to the definition.
     *
     * @param PropertyInterface $property The property information to add.
     *
     * @return PropertiesDefinitionInterface
     */
    public function addProperty($property): static;

    /**
     * Remove a property information from the definition.
     *
     * @param PropertyInterface|string $property The information or the name of the property to remove.
     *
     * @return PropertiesDefinitionInterface
     */
    public function removeProperty($property): static;

    /**
     * Check if a property exists.
     *
     * @param string $name The name of the property.
     *
     * @return bool
     */
    public function hasProperty($name): bool;

    /**
     * Get a property by name.
     *
     * @param string $name The name of the property.
     *
     * @return PropertyInterface
     */
    public function getProperty($name): PropertyInterface;
}
