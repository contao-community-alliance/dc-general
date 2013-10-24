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

namespace DcGeneral\DataDefinition;


use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;

class DefaultContainer implements ContainerInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var ContainerSectionInterface
	 */
	protected $sections;

	/**
	 * Create a new default container.
	 *
	 * @param string $name
	 */
	function __construct($name)
	{
		$this->name = (string) $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Check if this container has a section.
	 *
	 * @param string $sectionName
	 *
	 * @return bool
	 */
	public function hasSection($sectionName)
	{
		// TODO: Implement hasSection() method.
	}

	/**
	 * Clear all sections from this container.
	 *
	 * @return static
	 */
	public function clearSections()
	{

	}

	/**
	 * Set the sections of this container.
	 *
	 * @param ContainerSectionInterface[] $sections
	 *
	 * @return static
	 */
	public function setSections(array $sections)
	{

	}

	/**
	 * Add multiple sections to this container.
	 *
	 * @param ContainerSectionInterface[] $sections
	 *
	 * @return static
	 */
	public function addSections(array $sections)
	{

	}

	/**
	 * Set a sections of this container.
	 *
	 * @param string $sectionName
	 * @param ContainerSectionInterface $section
	 *
	 * @return static
	 */
	public function setSection($sectionName, ContainerSectionInterface $section)
	{

	}

	/**
	 * Remove a sections from this container.
	 *
	 * @param string $sectionName
	 *
	 * @return static
	 */
	public function removeSection($sectionName)
	{

	}

	/**
	 * Get a sections of this container.
	 *
	 * @param string $sectionName
	 *
	 * @return ContainerSectionInterface
	 *
	 * @throws DcGeneralInvalidArgumentException Is thrown when there is no section with this name.
	 */
	public function getSection($sectionName)
	{

	}

	/**
	 * Get a list of all section names in this container.
	 *
	 * @return array
	 */
	public function getSectionNames()
	{

	}
}
