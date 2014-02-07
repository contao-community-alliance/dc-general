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

namespace DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Interface SearchElementInformationInterface.
 *
 * This interface describes a search panel element information filtering on properties.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
interface SearchElementInformationInterface extends ElementInformationInterface
{
	/**
	 * Add a property name to the element.
	 *
	 * @param string $propertyName The property to allow to search on.
	 *
	 * @return SearchElementInformationInterface
	 */
	public function addProperty($propertyName);

	/**
	 * Retrieve the list of properties to allow search on.
	 *
	 * @return string[]
	 */
	public function getPropertyNames();
}
