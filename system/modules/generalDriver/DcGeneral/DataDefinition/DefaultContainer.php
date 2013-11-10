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


use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\DataDefinition\Section\ContainerSectionInterface;
use DcGeneral\DataDefinition\Section\DataProviderSectionInterface;
use DcGeneral\DataDefinition\Section\PalettesSectionInterface;
use DcGeneral\DataDefinition\Section\PropertiesSectionInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class DefaultContainer implements ContainerInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var ContainerSectionInterface[]
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
	 * {@inheritdoc}
	 */
	public function hasSection($sectionName)
	{
		return isset($this->sections[$sectionName]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearSections()
	{
		$this->sections = array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSections(array $sections)
	{
		$this
			->clearSections()
			->addSections($sections);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addSections(array $sections)
	{
		foreach ($sections as $name => $section)
		{
			if (!($section instanceof ContainerSectionInterface))
			{
				throw new DcGeneralInvalidArgumentException('Section ' . $name . ' does not implement ContainerSectionInterface.');
			}

			$this->setSection($name, $section);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSection($sectionName, ContainerSectionInterface $section)
	{
		$this->sections[$sectionName] = $section;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeSection($sectionName)
	{
		unset($this->sections[$sectionName]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSection($sectionName)
	{
		if (!$this->hasSection($sectionName))
		{
			throw new DcGeneralInvalidArgumentException('Section ' . $sectionName . ' is not registered in the configuration.');
		}

		return $this->sections[$sectionName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSectionNames()
	{
		return array_keys($this->sections);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasBasicSection()
	{
		return $this->hasSection(BasicSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setBasicSection(BasicSectionInterface $section)
	{
		return $this->setSection(BasicSectionInterface::NAME, $section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBasicSection()
	{
		return $this->getSection(BasicSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasPropertiesSection()
	{
		return $this->hasSection(PropertiesSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPropertiesSection(PropertiesSectionInterface $section)
	{
		return $this->setSection(PropertiesSectionInterface::NAME, $section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertiesSection()
	{
		return $this->getSection(PropertiesSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasPalettesSection()
	{
		return $this->hasSection(PalettesSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPalettesSection(PalettesSectionInterface $section)
	{
		return $this->setSection(PalettesSectionInterface::NAME, $section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPalettesSection()
	{
		return $this->getSection(PalettesSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasDataProviderSection()
	{
		return $this->hasSection(DataProviderSectionInterface::NAME);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDataProviderSection(DataProviderSectionInterface $section)
	{
		return $this->setSection(DataProviderSectionInterface::NAME, $section);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataProviderSection()
	{
		return $this->getSection(DataProviderSectionInterface::NAME);
	}
}
