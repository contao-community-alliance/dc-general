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

namespace DcGeneral\DataDefinition\Section;

use DcGeneral\DataDefinition\Section\Palette\PropertyInterface;

/**
 * Interface BasicSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
interface PropertiesSectionInterface extends ContainerSectionInterface
{
	/**
	 * The name of the section.
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
	 * @param PropertyInterface $property
	 *
	 * @return PropertiesSectionInterface
	 */
	public function addProperty($property);

	/**
	 * @param PropertyInterface|string $property
	 *
	 * @return PropertiesSectionInterface
	 */
	public function removeProperty($property);

	/**
	 * Check if a property exists.
	 * 
	 * @param string $name
	 * 
	 * @return bool
	 */
	public function hasProperty($name);

	/**
	 * Get a property by name.
	 *
	 * @param $name
	 *
	 * @return PropertyInterface
	 */
	public function getProperty($name);
}
