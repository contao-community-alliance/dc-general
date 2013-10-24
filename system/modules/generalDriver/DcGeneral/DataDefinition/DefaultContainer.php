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
}
