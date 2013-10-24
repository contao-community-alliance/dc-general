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

interface PluggableContainerParserInterface extends ContainerParserInterface
{
	/**
	 * Parse and return the section of a container.
	 *
	 * @param string $section The section's name e.g. "config" or "list".
	 *
	 * @return mixed
	 *
	 * @throws DcGeneralInvalidArgumentException Is thrown if there is no parser registered for this section.
	 */
	public function parseSection($section);

	/**
	 * Check if a parser for a specific section is registered.
	 *
	 * @param string $section The section's name e.g. "config" or "list".
	 *
	 * @return bool
	 */
	public function hasSectionParser($section);

	/**
	 * Set the parser for a specific section.
	 *
	 * @param string $section The section's name e.g. "config" or "list".
	 * @param ContainerSectionParserInterface|null $parser The parser for this section.
	 *
	 * @return mixed
	 */
	public function setSectionParser($section, ContainerSectionParserInterface $parser = null);

	/**
	 * Return the parser for a specific section.
	 *
	 * @param string $section The section's name e.g. "config" or "list".
	 *
	 * @return ContainerSectionParserInterface
	 *
	 * @throws DcGeneralInvalidArgumentException Is thrown if there is no parser registered for this section.
	 */
	public function getSectionParser($section);
}
