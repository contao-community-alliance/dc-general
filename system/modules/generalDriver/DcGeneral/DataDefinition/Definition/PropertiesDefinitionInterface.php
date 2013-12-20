<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Definition;

use DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;

/**
 * This interface describes the data definition that holds all property information.
 *
 * @package DcGeneral\DataDefinition\Definition
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
