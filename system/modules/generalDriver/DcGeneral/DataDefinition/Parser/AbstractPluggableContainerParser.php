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

namespace DcGeneral\DataDefinition\Parser;

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

abstract class AbstractPluggableContainerParser implements PluggableContainerParserInterface
{
	/**
	 * @var ContainerSectionParserInterface[]
	 */
	protected $sectionParsers = array();

	/**
	 * {@inheritdoc}
	 */
	public function parseSection($section)
	{
		if (!$this->hasSectionParser($section)) {
			throw new DcGeneralInvalidArgumentException('No section parser found for ' . $section);
		}

		return $this->sectionParsers[$section]->parseSection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasSectionParser($section)
	{
		return (bool) $this->sectionParsers[$section];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSectionParser($section, ContainerSectionParserInterface $parser = null)
	{
		$this->sectionParsers[$section] = $parser;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSectionParser($section)
	{
		if (!$this->hasSectionParser($section)) {
			throw new DcGeneralInvalidArgumentException('No section parser found for ' . $section);
		}

		return $this->sectionParsers[$section];
	}
}
